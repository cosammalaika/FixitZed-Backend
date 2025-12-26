<?php

use App\Livewire\Fixer\FixerEdit;
use App\Models\Category;
use App\Models\Fixer;
use App\Models\Service;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createService(): Service
{
    $category = Category::create([
        'name' => 'Category',
        'description' => 'Test category',
    ]);

    $subcategory = Subcategory::create([
        'category_id' => $category->id,
        'name' => 'Subcategory',
        'description' => 'Test subcategory',
    ]);

    return Service::create([
        'subcategory_id' => $subcategory->id,
        'name' => 'Test Service',
        'description' => 'Test service description',
        'price' => 10,
        'duration_minutes' => 30,
    ]);
}

it('handles status sent as array and saves the fixer', function () {
    $user = User::factory()->create(['status' => 'Active']);
    $fixer = Fixer::create([
        'user_id' => $user->id,
        'status' => 'pending',
        'bio' => 'Old bio',
    ]);

    $serviceA = createService();
    $serviceB = createService();

    Livewire::test(FixerEdit::class, ['id' => $fixer->id])
        ->set('bio', 'Updated bio')
        ->set('status', ['approved', 'pending'])
        ->set('selected_services', [$serviceA->id, $serviceB->id])
        ->call('submit')
        ->assertHasNoErrors()
        ->assertDispatchedBrowserEvent('flash-message');

    $fixer->refresh();
    expect($fixer->status)->toBe('approved')
        ->and($fixer->bio)->toBe('Updated bio')
        ->and($fixer->services()->pluck('services.id')->sort()->values()->all())
        ->toBe([$serviceA->id, $serviceB->id]);
});

it('fails validation gracefully when status array contains no value', function () {
    $user = User::factory()->create(['status' => 'Active']);
    $fixer = Fixer::create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    Livewire::test(FixerEdit::class, ['id' => $fixer->id])
        ->set('status', [])
        ->call('submit')
        ->assertHasErrors(['status' => ['required']]);
});
