<?php

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
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
