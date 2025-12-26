<?php

use App\Livewire\Fixer\FixerEdit;
use App\Models\Category;
use App\Models\Fixer;
use App\Models\Service;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createService(?Subcategory $subcategory = null): Service
{
    if (! $subcategory) {
        $category = Category::create([
            'name' => 'Category',
            'description' => 'Test category',
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Subcategory',
            'description' => 'Test subcategory',
        ]);
    }

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

it('loads all services once, preselects assigned services, and persists updates', function () {
    $category = Category::create([
        'name' => 'Category',
        'description' => 'Test category',
    ]);
    $subcategory = Subcategory::create([
        'category_id' => $category->id,
        'name' => 'Subcategory',
        'description' => 'Test subcategory',
    ]);

    $serviceA = createService($subcategory);
    $serviceB = createService($subcategory);
    $serviceC = createService($subcategory);

    $user = User::factory()->create(['status' => 'Active']);
    $fixer = Fixer::create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    // Introduce duplicate pivot rows to ensure they are deduplicated in the UI.
    $fixer->services()->attach([$serviceA->id, $serviceB->id]);
    DB::table('fixer_service')->insert([
        'fixer_id' => $fixer->id,
        'service_id' => $serviceB->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(FixerEdit::class, ['id' => $fixer->id])
        ->assertSet('selected_services', [
            (string) $serviceA->id,
            (string) $serviceB->id,
        ])
        ->assertViewHas('services', function ($services) use ($serviceA, $serviceB, $serviceC) {
            return $services->pluck('id')->sort()->values()->all() === [
                $serviceA->id,
                $serviceB->id,
                $serviceC->id,
            ];
        })
        ->set('selected_services', [(string) $serviceB->id, (string) $serviceC->id])
        ->call('submit')
        ->assertHasNoErrors()
        ->assertDispatchedBrowserEvent('flash-message');

    $fixer->refresh();
    $pivotIds = $fixer->services()->pluck('services.id')->sort()->values()->all();
    expect($pivotIds)->toBe([$serviceB->id, $serviceC->id]);
});
