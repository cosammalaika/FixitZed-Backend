<?php

namespace Tests\Feature;

use App\Models\Fixer;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RequestExpiryAndAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure sensible default expiry.
        Setting::set('requests.expiry_minutes', 15);
    }

    protected function makeService(): Service
    {
        return Service::create([
            'name' => 'AC Repair',
            'category' => 'Repairs',
            'description' => 'Test',
            'status' => 'active',
        ]);
    }

    public function test_services_endpoint_returns_data(): void
    {
        $service = $this->makeService();
        $countBefore = Service::count();

        $resp = $this->getJson('/api/services');
        $resp->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertNotEmpty($resp->json('data'));
        $this->assertTrue(
            collect($resp->json('data'))->contains(fn ($s) => (int) ($s['id'] ?? 0) === $service->id)
        );
        $this->assertSame($countBefore, Service::count(), 'GET /api/services must not mutate data');
    }

    public function test_request_stays_pending_and_is_assigned_to_eligible_fixer(): void
    {
        $service = $this->makeService();

        // Offset ids so fixer_id cannot equal user_id by coincidence.
        User::factory()->create();
        $fixerUser = User::factory()->create();
        $fixer = Fixer::create([
            'user_id' => $fixerUser->id,
            'status' => 'approved',
        ]);
        $fixer->services()->attach($service->id);

        $customer = User::factory()->create();

        $payload = [
            'service_id' => $service->id,
            'scheduled_at' => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
            'location' => 'Test Location',
        ];

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/requests', $payload)
            ->assertStatus(201)
            ->assertJson(['success' => true]);

        $sr = \App\Models\ServiceRequest::first();
        $this->assertNotNull($sr);
        $this->assertSame('pending', $sr->status);
        $this->assertSame($fixer->id, $sr->fixer_id);
        $this->assertNotSame($fixerUser->id, $sr->fixer_id, 'fixer_id must store fixer profile id, not user id');

        // Fixer feed should include it.
        $this->actingAs($fixerUser, 'sanctum')
            ->getJson('/api/fixer/requests')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $sr->id]);
    }

    public function test_unassigned_request_visible_and_not_expired_immediately(): void
    {
        $service = $this->makeService();

        // Fixer not approved => assignment should skip, leaving fixer_id null.
        $fixerUser = User::factory()->create();
        $fixer = Fixer::create([
            'user_id' => $fixerUser->id,
            'status' => 'pending',
        ]);
        $fixer->services()->attach($service->id);

        $customer = User::factory()->create();
        $payload = [
            'service_id' => $service->id,
            'scheduled_at' => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
            'location' => 'Test Location',
        ];

        $this->actingAs($customer, 'sanctum')
            ->postJson('/api/requests', $payload)
            ->assertStatus(201);

        $sr = \App\Models\ServiceRequest::first();
        $this->assertNotNull($sr);
        $this->assertSame('pending', $sr->status);
        $this->assertNull($sr->fixer_id);

        // Immediately after creation, it should not be expired and should appear in fixer feed via pivot.
        $this->actingAs($fixerUser, 'sanctum')
            ->getJson('/api/fixer/requests')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $sr->id]);
    }
}
