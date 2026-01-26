<?php

namespace Tests\Feature;

use App\Livewire\Fixer\FixerCreate;
use App\Livewire\Fixer\FixerEdit;
use App\Models\Fixer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FixerServiceDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected function makeService(): Service
    {
        return Service::create([
            'name' => 'AC Repair',
            'category' => 'Repairs',
            'description' => 'Test',
            'status' => 'active',
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
