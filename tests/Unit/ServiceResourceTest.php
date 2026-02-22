<?php

use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;

it('maps normalized service activation fields to the legacy status contract', function () {
    $service = new Service();
    $service->setRawAttributes([
        'id' => 99,
        'name' => 'Leak Repair',
        'category' => 'Plumbing',
        'description' => 'Fix leaking pipes',
        'is_active' => 1,
    ], true);
    $service->setAttribute('fixers_count', 4);

    $payload = (new ServiceResource($service))->toArray(Request::create('/api/services', 'GET'));

    expect($payload['is_active'])->toBeTrue()
        ->and($payload['active'])->toBeTrue()
        ->and($payload['status'])->toBe('active')
        ->and($payload['category'])->toBe('Plumbing')
        ->and($payload['category_name'])->toBe('Plumbing')
        ->and($payload['subcategory_name'])->toBe('Plumbing')
        ->and($payload['category_id'])->toBeInt()
        ->and($payload['subcategory_id'])->toBe($payload['category_id'])
        ->and($payload['fixers_count'])->toBe(4)
        ->and(array_key_exists('price', $payload))->toBeFalse()
        ->and(array_key_exists('duration_minutes', $payload))->toBeFalse();
});
