<?php

use Livewire\Volt\Volt;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = Volt::test('auth.register')
        ->set('first_name', 'Test')
        ->set('last_name', 'User')
        ->set('username', 'testuser')
        ->set('email', 'test@example.com')
        ->set('contact_number', '123456789')
        ->set('status', 'Active')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});
