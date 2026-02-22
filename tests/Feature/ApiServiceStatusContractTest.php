<?php

use App\Models\Category;
use App\Models\Service;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Schema;

it('returns active and inactive status aliases in the services api response', function () {
    $activeService = null;
    $inactiveService = null;

    if (Schema::hasColumn('services', 'subcategory_id') && Schema::hasTable('subcategories') && Schema::hasTable('categories')) {
        $category = Category::query()->create([
            'name' => 'Test Category',
            'description' => 'Feature test category',
        ]);

        $subcategory = Subcategory::query()->create([
            'category_id' => $category->id,
            'name' => 'Test Subcategory',
            'description' => 'Feature test subcategory',
        ]);

        $activeService = Service::query()->create([
            'name' => 'API Status Active',
            'description' => 'Active service',
            'subcategory_id' => $subcategory->id,
            'is_active' => true,
        ]);

        $inactiveService = Service::query()->create([
            'name' => 'API Status Inactive',
            'description' => 'Inactive service',
            'subcategory_id' => $subcategory->id,
            'is_active' => false,
        ]);
    } else {
        $activeService = Service::query()->create([
            'name' => 'API Status Active',
            'category' => 'General',
            'description' => 'Active service',
            'status' => 'active',
        ]);

        $inactiveService = Service::query()->create([
            'name' => 'API Status Inactive',
            'category' => 'General',
            'description' => 'Inactive service',
            'status' => 'inactive',
        ]);
    }

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
        ->and(array_key_exists('category', $active))->toBeTrue()
        ->and(array_key_exists('category_id', $active))->toBeTrue()
        ->and(array_key_exists('subcategory_id', $active))->toBeTrue();

    expect($inactive['is_active'])->toBeFalse()
        ->and($inactive['active'])->toBeFalse()
        ->and($inactive['status'])->toBe('inactive');
});
