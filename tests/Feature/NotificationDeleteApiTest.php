<?php

use App\Models\Notification;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('deletes the authenticated users own notification', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'recipient_type' => 'Individual',
        'user_id' => $user->id,
        'title' => 'Test notification',
        'message' => 'Delete me',
        'read' => false,
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/notifications/{$notification->id}")
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);

    $this->assertSoftDeleted('notifications', [
        'id' => $notification->id,
    ]);
});

it('returns 404 when the notification does not belong to the authenticated user', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $notification = Notification::create([
        'recipient_type' => 'Individual',
        'user_id' => $owner->id,
        'title' => 'Private notification',
        'message' => 'Do not delete me',
        'read' => false,
    ]);

    Sanctum::actingAs($otherUser);

    $this->deleteJson("/api/notifications/{$notification->id}")
        ->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Notification not found.',
        ]);

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'deleted_at' => null,
    ]);
});

it('returns 404 for shared notifications', function () {
    $user = User::factory()->create();
    $notification = Notification::create([
        'recipient_type' => 'Customer',
        'user_id' => null,
        'title' => 'Shared notification',
        'message' => 'This is visible to a broader audience',
        'read' => false,
    ]);

    Sanctum::actingAs($user);

    $this->deleteJson("/api/notifications/{$notification->id}")
        ->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Notification not found.',
        ]);

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'deleted_at' => null,
    ]);
});
