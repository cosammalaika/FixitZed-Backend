<?php

use App\Models\Service;

it('returns active and inactive status aliases in the services api response', function () {
    $activeService = Service::query()->create([
        'name' => 'API Status Active',
        'category' => 'General',
        'description' => 'Active service',
        'is_active' => true,
    ]);

    $inactiveService = Service::query()->create([
        'name' => 'API Status Inactive',
        'category' => 'General',
        'description' => 'Inactive service',
        'is_active' => false,
    ]);

    $response = $this->getJson('/api/services');

    $response->assertOk()->assertJson(['success' => true]);

    $items = collect($response->json('data'));

    $active = $items->firstWhere('id', $activeService->id);
    $inactive = $items->firstWhere('id', $inactiveService->id);

    expect($active)->not->toBeNull();
    expect($inactive)->not->toBeNull();

    expect($active['is_active'])->toBeTrue()
        ->and($active['active'])->toBeTrue()
        ->and($active['status'])->toBe('active')
        ->and($active['category'])->toBe('General')
        ->and(array_key_exists('category_id', $active))->toBeTrue()
        ->and(array_key_exists('subcategory_id', $active))->toBeTrue()
        ->and(array_key_exists('price', $active))->toBeFalse()
        ->and(array_key_exists('duration_minutes', $active))->toBeFalse();

    expect($inactive['is_active'])->toBeFalse()
        ->and($inactive['active'])->toBeFalse()
        ->and($inactive['status'])->toBe('inactive');
});
