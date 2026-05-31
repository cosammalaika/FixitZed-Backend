<?php

use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use App\Jobs\DispatchNotificationPush;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\postJson;

it('reassigns a token to the latest authenticated user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $token = 'sample-token-123';

    // First owner
    Sanctum::actingAs($userA);
    postJson('/api/device-tokens', [
        'token' => $token,
        'platform' => 'android',
        'app' => 'customer',
    ])->assertOk();

    expect(DeviceToken::where('token', $token)->value('user_id'))->toBe($userA->id);

    // Re-register same token under different user
    Sanctum::actingAs($userB);
    postJson('/api/device-tokens', [
        'token' => $token,
        'platform' => 'ios',
        'app' => 'fixer',
    ])->assertOk();

    $row = DeviceToken::where('token', $token)->firstOrFail();
    expect($row->user_id)->toBe($userB->id)
        ->and($row->app)->toBe('fixer')
        ->and($row->platform)->toBe('ios');
});

it('filters tokens by app when sending', function () {
    $user = User::factory()->create();

    DeviceToken::create([
        'user_id' => $user->id,
        'token' => 'customer-token',
        'platform' => 'android',
        'app' => 'customer',
    ]);
    DeviceToken::create([
        'user_id' => $user->id,
        'token' => 'fixer-token',
        'platform' => 'ios',
        'app' => 'fixer',
    ]);

    // Fake HTTP so no real FCM calls happen
    Http::fake();

    $captured = [];
    $this->app->bind(App\Services\FcmService::class, function () use (&$captured) {
        return new class($captured) extends App\Services\FcmService {
            public array $captured;
            public function __construct(&$captured)
            {
                $this->captured = &$captured;
                $this->projectId = 'test';
                $this->enabled = true;
            }
            protected function accessToken(): ?string
            {
                return 'test-token';
            }
            public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
            {
                $this->captured = $tokens;
            }
        };
    });

    $svc = $this->app->make(App\Services\FcmService::class);
    $svc->sendToUser($user, 'Hi', 'Body', [], 'fixer');

    expect($captured)->toBe(['fixer-token']);
});

it('accepts app_type on the singular device-token endpoint', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    postJson('/api/device-token', [
        'token' => 'singular-endpoint-token',
        'platform' => 'android',
        'app_type' => 'customer',
    ])->assertOk()
        ->assertJsonPath('data.app_type', 'customer');

    $row = DeviceToken::where('token', 'singular-endpoint-token')->firstOrFail();

    expect($row->user_id)->toBe($user->id)
        ->and($row->app)->toBe('customer');
});

it('queues push delivery whenever a notification record is created', function () {
    config()->set('services.fcm.queue_notifications', true);

    Queue::fake();

    $notification = Notification::create([
        'recipient_type' => 'Customer',
        'title' => 'Broadcast',
        'message' => 'Hello customers',
    ]);

    Queue::assertPushed(DispatchNotificationPush::class, function (DispatchNotificationPush $job) use ($notification) {
        return $job->notificationId === $notification->id;
    });
});

it('sends push immediately by default when a notification record is created', function () {
    config()->set('services.fcm.queue_notifications', false);

    $captured = [];
    $this->app->bind(App\Services\FcmService::class, function () use (&$captured) {
        return new class($captured) extends App\Services\FcmService {
            public array $captured;

            public function __construct(&$captured)
            {
                $this->captured = &$captured;
                $this->projectId = 'test';
                $this->enabled = true;
            }

            public function sendNotificationRecord(Notification $notification): void
            {
                $this->captured = [
                    'notification_id' => $notification->id,
                    'recipient_type' => $notification->recipient_type,
                    'title' => $notification->title,
                ];
            }
        };
    });

    $notification = Notification::create([
        'recipient_type' => 'Customer',
        'title' => 'Immediate push',
        'message' => 'Hello customers',
    ]);

    expect($captured)->toBe([
        'notification_id' => $notification->id,
        'recipient_type' => 'Customer',
        'title' => 'Immediate push',
    ]);
});

it('dispatches audience notifications only to matching active app tokens', function () {
    Role::findOrCreate('Customer', 'web');
    Role::findOrCreate('Fixer', 'web');

    $customer = User::factory()->create(['status' => 'Active']);
    $customer->assignRole('Customer');

    $customerInactive = User::factory()->create(['status' => 'Inactive']);
    $customerInactive->assignRole('Customer');

    $fixer = User::factory()->create(['status' => 'Active']);
    $fixer->assignRole('Fixer');

    DeviceToken::create([
        'user_id' => $customer->id,
        'token' => 'customer-token',
        'platform' => 'android',
        'app' => 'customer',
    ]);
    DeviceToken::create([
        'user_id' => $customerInactive->id,
        'token' => 'inactive-customer-token',
        'platform' => 'android',
        'app' => 'customer',
    ]);
    DeviceToken::create([
        'user_id' => $fixer->id,
        'token' => 'fixer-token',
        'platform' => 'android',
        'app' => 'fixer',
    ]);

    $notification = Notification::create([
        'recipient_type' => 'Customer',
        'title' => 'Broadcast',
        'message' => 'Hello customers',
        'data' => ['sync_topics' => 'notifications'],
    ]);

    $captured = [];
    $this->app->bind(App\Services\FcmService::class, function () use (&$captured) {
        return new class($captured) extends App\Services\FcmService {
            public array $captured;

            public function __construct(&$captured)
            {
                $this->captured = &$captured;
                $this->projectId = 'test';
                $this->enabled = true;
            }

            protected function accessToken(): ?string
            {
                return 'test-token';
            }

            public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
            {
                $this->captured = [
                    'tokens' => $tokens,
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                ];
            }
        };
    });

    $service = $this->app->make(App\Services\FcmService::class);
    $service->sendNotificationRecord($notification->fresh());

    expect($captured['tokens'] ?? null)->toBe(['customer-token'])
        ->and($captured['data']['app'] ?? null)->toBe('customer')
        ->and($captured['data']['app_type'] ?? null)->toBe('customer')
        ->and($captured['data']['payload'] ?? null)->toBe("remote_notification:{$notification->id}");
});
