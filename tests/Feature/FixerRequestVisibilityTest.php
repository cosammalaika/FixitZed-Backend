<?php

namespace Tests\Feature;

use App\Models\Fixer;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixerRequestVisibilityTest extends TestCase
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

    public function test_assigned_request_is_visible_in_fixer_feed(): void
    {
        $service = $this->makeService();

        $fixerUser = User::factory()->create();
        $fixer = Fixer::create([
            'user_id' => $fixerUser->id,
            'status' => 'approved',
            'bio' => '',
        ]);
        $fixer->services()->attach($service->id);

        $customer = User::factory()->create();

        $payload = [
            'service_id' => $service->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'location' => 'Test Location',
        ];

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/requests', $payload)
            ->assertStatus(201);

        $sr = ServiceRequest::first();
        $this->assertNotNull($sr);
        $this->assertSame($fixer->id, $sr->fixer_id);

        $this->actingAs($fixerUser, 'sanctum')
            ->getJson('/api/fixer/requests')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $sr->id]);
    }

    public function test_request_is_rejected_when_no_eligible_fixer_exists(): void
    {
        $service = $this->makeService();
        $customer = User::factory()->create();

        $payload = [
            'service_id' => $service->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'location' => 'Test Location',
        ];

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/requests', $payload)
            ->assertStatus(422)
            ->assertJsonFragment(['success' => false]);

        $this->assertSame(0, ServiceRequest::count());
    }
}
