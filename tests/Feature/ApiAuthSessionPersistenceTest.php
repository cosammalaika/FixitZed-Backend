<?php

use App\Models\User;

it('keeps existing sanctum tokens when a user logs in again', function () {
    $user = User::factory()->create();

    $user->createToken('mobile');

    $response = $this->postJson('/api/login', [
        'identifier' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    expect($user->fresh()->tokens()->count())->toBe(2);
});

it('blocks disabled accounts from logging in', function () {
    $user = User::factory()->create([
        'status' => 'Inactive',
    ]);

    $response = $this->postJson('/api/login', [
        'identifier' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(423)
        ->assertJsonPath('code', 'account_disabled');

    expect($user->fresh()->tokens()->count())->toBe(0);
});

it('revokes the current sanctum token when an account becomes disabled', function () {
    $user = User::factory()->create([
        'status' => 'Active',
    ]);

    $token = $user->createToken('mobile')->plainTextToken;
    $user->forceFill([
        'status' => 'Inactive',
    ])->save();

    $response = $this
        ->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/me');

    $response->assertStatus(423)
        ->assertJsonPath('code', 'account_disabled');

    expect($user->fresh()->tokens()->count())->toBe(0);
});
