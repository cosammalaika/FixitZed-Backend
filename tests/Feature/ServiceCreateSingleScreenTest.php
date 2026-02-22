<?php

use App\Livewire\Service\ServiceCreate;
use App\Models\Service;
use Livewire\Livewire;

it('creates a service from the single screen fields only', function () {
    Livewire::test(ServiceCreate::class)
        ->set('name', 'Single Screen Create Test')
        ->set('category', 'Testing Category')
        ->set('description', 'Created from Livewire test')
        ->set('status', 'inactive')
        ->call('submit')
        ->assertHasNoErrors();

    $service = Service::query()->where('name', 'Single Screen Create Test')->first();

    expect($service)->not->toBeNull()
        ->and($service->category)->toBe('Testing Category')
        ->and($service->is_active)->toBeFalse()
        ->and($service->status)->toBe('inactive');
});
