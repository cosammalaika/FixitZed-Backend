<?php

use App\Models\Category;
use App\Models\Fixer;
use App\Models\FixerWallet;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestDecline;
use App\Models\Setting;
use App\Models\Subcategory;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function createServiceWithFixers(int $fixerCount = 2): array
{
    $category = Category::create([
        'name' => 'Home Services',
        'description' => 'Test category',
    ]);
    $subcategory = Subcategory::create([
        'category_id' => $category->id,
        'name' => 'Repairs',
        'description' => 'Test subcategory',
    ]);
    $service = Service::create([
        'subcategory_id' => $subcategory->id,
        'name' => 'Plumbing',
        'description' => 'Test service',
        'price' => 50,
        'duration_minutes' => 60,
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
