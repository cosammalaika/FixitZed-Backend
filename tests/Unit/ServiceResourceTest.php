<?php

use App\Http\Resources\ServiceResource;
use App\Models\Category;
use App\Models\Service;
use App\Models\Subcategory;
use Illuminate\Http\Request;

it('maps normalized service activation fields to the legacy status contract', function () {
    $category = new Category();
    $category->setRawAttributes([
        'id' => 1,
        'name' => 'All Services',
        'description' => null,
    ], true);

    $subcategory = new Subcategory();
    $subcategory->setRawAttributes([
        'id' => 10,
        'category_id' => 1,
        'name' => 'Plumbing',
        'description' => null,
    ], true);
    $subcategory->setRelation('category', $category);

    $service = new Service();
    $service->setRawAttributes([
        'id' => 99,
        'subcategory_id' => 10,
        'name' => 'Leak Repair',
        'description' => 'Fix leaking pipes',
        'price' => '150.00',
        'duration_minutes' => 60,
        'is_active' => 1,
    ], true);
    $service->setRelation('subcategory', $subcategory);
    $service->setAttribute('fixers_count', 4);

    $payload = (new ServiceResource($service))->toArray(Request::create('/api/services', 'GET'));

    expect($payload['is_active'])->toBeTrue()
        ->and($payload['active'])->toBeTrue()
        ->and($payload['status'])->toBe('active')
        ->and($payload['category'])->toBe('Plumbing')
        ->and($payload['category_id'])->toBe(1)
        ->and($payload['subcategory_id'])->toBe(10)
        ->and($payload['fixers_count'])->toBe(4);
});
