<?php

use App\Models\User;

test('authenticated users can delete their own account through the api', function () {
    $user = User::factory()->create([
        'email' => 'customer.delete@example.com',
        'username' => 'customer_delete',
        'contact_number' => '+260971234567',
        'address' => 'Private address',
    ]);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->deleteJson('/api/me')
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Your account has been permanently deleted.',
        ]);

    $this->assertSoftDeleted('users', ['id' => $user->id]);

    $deleted = User::withTrashed()->findOrFail($user->id);
    expect($deleted->email)->not->toBe('customer.delete@example.com');
    expect($deleted->username)->toStartWith('deleted_user_'.$user->id);
    expect($deleted->contact_number)->not->toBe('+260971234567');
    expect($deleted->address)->toBeNull();
    expect($deleted->status)->toBe('Deleted');
    expect($deleted->tokens()->count())->toBe(0);

    auth()->forgetGuards();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/me')
        ->assertUnauthorized();
});
