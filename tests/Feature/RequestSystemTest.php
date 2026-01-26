<?php

use App\Models\Fixer;
use App\Models\FixerWallet;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestDecline;
use App\Models\Setting;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\patchJson;

function createServiceWithFixers(int $fixerCount = 2): array
{
    $service = Service::create([
        'name' => 'Plumbing',
        'category' => 'Repairs',
        'description' => 'Test service',
        'status' => 'active',
    ]);

    $fixers = [];
    for ($i = 0; $i < $fixerCount; $i++) {
        $user = User::factory()->create();
        $fixer = Fixer::create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);
        FixerWallet::create([
            'fixer_id' => $fixer->id,
            'coin_balance' => 5,
            'subscription_status' => 'approved',
        ]);
        $service->fixers()->attach($fixer->id);
        $fixers[] = $fixer;
    }

    return [$service, $fixers];
}

function createServiceRequest(Service $service, ?Fixer $fixer = null): ServiceRequest
{
    $customer = User::factory()->create();
    return ServiceRequest::create([
        'customer_id' => $customer->id,
        'fixer_id' => $fixer?->id,
        'service_id' => $service->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'pending',
        'location' => 'Test location',
    ]);
}

it('allows only one fixer to accept a pending request', function () {
    Setting::set('requests.expiry_minutes', 60);
    [$service, $fixers] = createServiceWithFixers(2);
    $sr = createServiceRequest($service);

    $fixerA = $fixers[0];
    $fixerB = $fixers[1];

    Sanctum::actingAs($fixerA->user);
    $resA = postJson("/api/service-requests/{$sr->id}/accept", []);
    $resA->assertOk();

    Sanctum::actingAs($fixerB->user);
    $resB = postJson("/api/service-requests/{$sr->id}/accept", []);
    $resB->assertStatus(409);

    $sr->refresh();
    expect($sr->status)->toBe('accepted')
        ->and($sr->fixer_id)->toBe($fixerA->id);
});

it('decline is idempotent per fixer', function () {
    Setting::set('requests.expiry_minutes', 60);
    [$service, $fixers] = createServiceWithFixers(1);
    $sr = createServiceRequest($service);

    $fixer = $fixers[0];
    Sanctum::actingAs($fixer->user);

    postJson("/api/fixer/requests/{$sr->id}/decline", [])->assertOk();
    postJson("/api/fixer/requests/{$sr->id}/decline", [])->assertOk();

    expect(ServiceRequestDecline::where('service_request_id', $sr->id)->count())->toBe(1);
});

it('decline does not block another fixer from accepting', function () {
    Setting::set('requests.expiry_minutes', 60);
    [$service, $fixers] = createServiceWithFixers(2);
    $sr = createServiceRequest($service);

    $fixerA = $fixers[0];
    $fixerB = $fixers[1];

    Sanctum::actingAs($fixerA->user);
    postJson("/api/fixer/requests/{$sr->id}/decline", [])->assertOk();

    Sanctum::actingAs($fixerB->user);
    postJson("/api/service-requests/{$sr->id}/accept", [])->assertOk();

    $sr->refresh();
    expect($sr->status)->toBe('accepted')
        ->and($sr->fixer_id)->toBe($fixerB->id);
});

it('expired requests cannot be accepted or declined', function () {
    Setting::set('requests.expiry_minutes', 1);
    [$service, $fixers] = createServiceWithFixers(1);
    $sr = createServiceRequest($service);
    $sr->forceFill([
        'created_at' => now()->subMinutes(5),
        'updated_at' => now()->subMinutes(5),
    ])->save();

    $fixer = $fixers[0];
    Sanctum::actingAs($fixer->user);

    postJson("/api/service-requests/{$sr->id}/accept", [])->assertStatus(410);
    postJson("/api/fixer/requests/{$sr->id}/decline", [])->assertStatus(410);

    $sr->refresh();
    expect($sr->status)->toBe('expired');
});

it('pending list excludes declined requests for the fixer', function () {
    Setting::set('requests.expiry_minutes', 60);
    [$service, $fixers] = createServiceWithFixers(1);
    $sr = createServiceRequest($service);
    $fixer = $fixers[0];

    Sanctum::actingAs($fixer->user);
    postJson("/api/fixer/requests/{$sr->id}/decline", [])->assertOk();

    $res = getJson('/api/fixer/requests?status=pending');
    $res->assertOk();

    $list = $res->json('data.data') ?? $res->json('data') ?? [];
    $ids = collect($list)->pluck('id')->all();
    expect($ids)->not->toContain($sr->id);
});

it('services endpoint returns data and does not write', function () {
    Service::truncate();
    $service = Service::create([
        'name' => 'AC Repair',
        'category' => 'HVAC',
        'description' => 'Test',
        'status' => 'active',
    ]);

    $countBefore = Service::count();
    $resp = getJson('/api/services');
    $resp->assertOk()
        ->assertJson([
            'success' => true,
            'meta' => ['count' => 1],
        ]);
    expect(Service::count())->toBe($countBefore);
    $resp->assertJsonFragment(['name' => 'AC Repair', 'category' => 'HVAC']);
});

it('services endpoint returns empty list when no services exist', function () {
    Service::truncate();
    $resp = getJson('/api/services');
    $resp->assertOk()
        ->assertJson([
            'success' => true,
            'meta' => ['count' => 0],
            'data' => [],
        ]);
});

it('services active endpoint returns only active', function () {
    Service::truncate();
    Service::create(['name' => 'Active One', 'category' => 'General', 'status' => 'active']);
    Service::create(['name' => 'Inactive One', 'category' => 'General', 'status' => 'inactive']);

    $resp = getJson('/api/services/active');
    $resp->assertOk()
        ->assertJson([
            'success' => true,
            'meta' => ['count' => 1],
        ]);
    $names = collect($resp->json('data'))->pluck('name')->all();
    expect($names)->toContain('Active One');
    expect($names)->not->toContain('Inactive One');
});

it('services endpoint ignores inactive services and does not depend on category tables', function () {
    Service::truncate();
    // Ensure legacy tables being empty does not block listings
    if (Schema::hasTable('categories')) {
        DB::table('categories')->truncate();
    }
    if (Schema::hasTable('subcategories')) {
        DB::table('subcategories')->truncate();
    }

    Service::create(['name' => 'Visible', 'category' => 'General', 'status' => 'active']);
    Service::create(['name' => 'Hidden', 'category' => 'General', 'status' => 'inactive']);

    $resp = getJson('/api/services');
    $resp->assertOk()
        ->assertJson([
            'success' => true,
            'meta' => ['count' => 1],
        ]);
    $names = collect($resp->json('data'))->pluck('name')->all();
    expect($names)->toContain('Visible');
    expect($names)->not->toContain('Hidden');
});

it('fixer billing sets awaiting_payment and is visible to customer', function () {
    Service::truncate();
    ServiceRequest::truncate();
    Payment::truncate();

    $service = Service::create(['name' => 'AC Repair', 'category' => 'HVAC', 'status' => 'active']);

    $fixerUser = User::factory()->create();
    $fixer = Fixer::create(['user_id' => $fixerUser->id, 'status' => 'approved']);
    FixerWallet::create(['fixer_id' => $fixer->id, 'coin_balance' => 5, 'subscription_status' => 'approved']);
    $service->fixers()->sync([$fixer->id]);

    $customer = User::factory()->create();
    $sr = ServiceRequest::create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'pending',
        'location' => 'Test',
    ]);

    Sanctum::actingAs($fixerUser);
    postJson("/api/service-requests/{$sr->id}/accept", [])->assertOk();

    $billRes = postJson("/api/fixer/requests/{$sr->id}/bill", ['amount' => 50])->assertOk();
    $billRes->assertJson(['success' => true]);

    $sr->refresh();
    expect($sr->status)->toBe('awaiting_payment');
    $payment = Payment::where('service_request_id', $sr->id)->first();
    expect($payment)->not()->toBeNull();
    expect((float) $payment->amount)->toBe(50.0);
    expect($payment->status)->toBe('pending');

    Sanctum::actingAs($customer);
    $detail = getJson("/api/requests/{$sr->id}")->assertOk();
    expect($detail->json('data.status'))->toBe('awaiting_payment');
});

it('decline keeps request pending for other eligible fixers and hides from decliner', function () {
    Service::truncate();
    ServiceRequest::truncate();
    ServiceRequestDecline::truncate();

    $service = Service::create(['name' => 'Cleaning', 'category' => 'Home', 'status' => 'active']);

    $fixerUserA = User::factory()->create();
    $fixerA = Fixer::create(['user_id' => $fixerUserA->id, 'status' => 'approved']);
    FixerWallet::create(['fixer_id' => $fixerA->id, 'coin_balance' => 5, 'subscription_status' => 'approved']);

    $fixerUserB = User::factory()->create();
    $fixerB = Fixer::create(['user_id' => $fixerUserB->id, 'status' => 'approved']);
    FixerWallet::create(['fixer_id' => $fixerB->id, 'coin_balance' => 5, 'subscription_status' => 'approved']);

    $service->fixers()->sync([$fixerA->id, $fixerB->id]);

    $customer = User::factory()->create();
    $sr = ServiceRequest::create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'pending',
        'location' => 'Test',
    ]);

    Sanctum::actingAs($fixerUserA);
    postJson("/api/fixer/requests/{$sr->id}/decline", [])->assertOk();
    $sr->refresh();
    expect($sr->status)->toBe('pending');
    expect($sr->fixer_id)->toBeNull();

    $resA = getJson('/api/fixer/requests?status=pending')->assertOk();
    $idsA = collect($resA->json('data.data') ?? $resA->json('data') ?? [])->pluck('id')->all();
    expect($idsA)->not()->toContain($sr->id);

    Sanctum::actingAs($fixerUserB);
    $resB = getJson('/api/fixer/requests?status=pending')->assertOk();
    $idsB = collect($resB->json('data.data') ?? $resB->json('data') ?? [])->pluck('id')->all();
    expect($idsB)->toContain($sr->id);
});

it('cancel vs accept race yields single outcome', function () {
    Service::truncate();
    ServiceRequest::truncate();

    $service = Service::create(['name' => 'Paint', 'category' => 'Home', 'status' => 'active']);
    $fixerUser = User::factory()->create();
    $fixer = Fixer::create(['user_id' => $fixerUser->id, 'status' => 'approved']);
    FixerWallet::create(['fixer_id' => $fixer->id, 'coin_balance' => 5, 'subscription_status' => 'approved']);
    $service->fixers()->sync([$fixer->id]);

    $customer = User::factory()->create();
    $sr = ServiceRequest::create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'pending',
        'location' => 'Test',
    ]);

    Sanctum::actingAs($customer);
    postJson("/api/requests/{$sr->id}/cancel", [])->assertOk();

    Sanctum::actingAs($fixerUser);
    postJson("/api/service-requests/{$sr->id}/accept", [])->assertStatus(410);

    $sr->refresh();
    expect($sr->status)->toBe('cancelled');
});

it('only eligible fixers can see or accept a request', function () {
    Service::truncate();
    ServiceRequest::truncate();

    $service = Service::create(['name' => 'Roof', 'category' => 'Home', 'status' => 'active']);

    $eligibleUser = User::factory()->create();
    $eligibleFixer = Fixer::create(['user_id' => $eligibleUser->id, 'status' => 'approved']);
    FixerWallet::create(['fixer_id' => $eligibleFixer->id, 'coin_balance' => 5, 'subscription_status' => 'approved']);

    $ineligibleUser = User::factory()->create();
    $ineligibleFixer = Fixer::create(['user_id' => $ineligibleUser->id, 'status' => 'approved']);
    FixerWallet::create(['fixer_id' => $ineligibleFixer->id, 'coin_balance' => 5, 'subscription_status' => 'approved']);

    $service->fixers()->sync([$eligibleFixer->id]); // ineligible not linked

    $customer = User::factory()->create();
    $sr = ServiceRequest::create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'pending',
        'location' => 'Test',
    ]);

    Sanctum::actingAs($ineligibleUser);
    $feedIneligible = getJson('/api/fixer/requests?status=pending')->assertOk();
    $idsIneligible = collect($feedIneligible->json('data.data') ?? $feedIneligible->json('data') ?? [])->pluck('id')->all();
    expect($idsIneligible)->not()->toContain($sr->id);
    postJson("/api/service-requests/{$sr->id}/accept", [])->assertStatus(403);

    Sanctum::actingAs($eligibleUser);
    $feedEligible = getJson('/api/fixer/requests?status=pending')->assertOk();
    $idsEligible = collect($feedEligible->json('data.data') ?? $feedEligible->json('data') ?? [])->pluck('id')->all();
    expect($idsEligible)->toContain($sr->id);
    postJson("/api/service-requests/{$sr->id}/accept", [])->assertOk();
    $sr->refresh();
    expect($sr->status)->toBe('accepted');
    expect($sr->fixer_id)->toBe($eligibleFixer->id);
});

it('customer token cannot call fixer accept endpoint', function () {
    Service::truncate();
    ServiceRequest::truncate();

    $service = Service::create(['name' => 'Drain', 'category' => 'Home', 'status' => 'active']);
    $customer = User::factory()->create();
    $sr = ServiceRequest::create([
        'customer_id' => $customer->id,
        'service_id' => $service->id,
        'scheduled_at' => now()->addDay(),
        'status' => 'pending',
        'location' => 'Test',
    ]);

    Sanctum::actingAs($customer);
    postJson("/api/service-requests/{$sr->id}/accept", [])->assertStatus(403);
});
