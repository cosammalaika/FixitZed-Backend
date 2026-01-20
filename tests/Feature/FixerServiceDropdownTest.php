<?php

namespace Tests\Feature;

use App\Livewire\Fixer\FixerCreate;
use App\Livewire\Fixer\FixerEdit;
use App\Models\Category;
use App\Models\Fixer;
use App\Models\Service;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FixerServiceDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected function makeService(): Service
    {
        $category = Category::create(['name' => 'Home', 'description' => '']);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Repairs',
            'description' => '',
        ]);

        return Service::create([
            'subcategory_id' => $subcategory->id,
            'name' => 'AC Repair',
            'description' => 'Test',
            'price' => 100,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);
    }

    public function test_edit_component_toggles_service_dropdown(): void
    {
        $service = $this->makeService();
        $user = User::factory()->create();
        $fixer = Fixer::create([
            'user_id' => $user->id,
            'status' => 'approved',
            'bio' => 'Test',
        ]);
        $fixer->services()->sync([$service->id]);

        Livewire::test(FixerEdit::class, ['id' => $fixer->id])
            ->call('toggleServiceDropdown')
            ->assertSet('showServiceDropdown', true)
            ->call('toggleServiceDropdown')
            ->assertSet('showServiceDropdown', false);
    }

    public function test_create_component_toggles_service_dropdown(): void
    {
        $this->makeService();
        User::factory()->create(); // ensure a selectable user exists

        Livewire::test(FixerCreate::class)
            ->call('toggleServiceDropdown')
            ->assertSet('showServiceDropdown', true)
            ->call('toggleServiceDropdown')
            ->assertSet('showServiceDropdown', false);
    }
}
