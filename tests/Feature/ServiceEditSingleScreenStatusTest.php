<?php

use App\Livewire\Service\ServiceEdit;
use App\Models\Service;
use Livewire\Livewire;

it('updates service status and category from the single screen without legacy category column queries', function () {
    $service = Service::query()->create([
        'name' => 'Single Screen Edit Test',
        'category' => 'Electrical',
        'description' => 'Initial description',
        'is_active' => true,
    ]);

    Livewire::test(ServiceEdit::class, ['id' => $service->id])
        ->set('name', 'Single Screen Edit Test')
        ->set('category', 'HVAC Services')
        ->set('description', 'Updated description')
        ->set('status', 'inactive')
        ->call('update')
        ->assertHasNoErrors();

    $service->refresh();

    expect($service->category)->toBe('HVAC Services')
        ->and($service->is_active)->toBeFalse()
        ->and($service->status)->toBe('inactive');
});
