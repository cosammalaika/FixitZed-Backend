<?php

use App\Models\District;
use App\Models\Province;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function antiFraudRegistrationPayload(array $overrides = []): array
{
    $province = Province::firstOrCreate(
        ['slug' => 'lusaka'],
        ['name' => 'Lusaka']
    );

    $district = District::firstOrCreate(
        ['province_id' => $province->id, 'slug' => 'lusaka-central'],
        ['name' => 'Lusaka Central']
    );

    return array_merge([
        'first_name' => 'Chanda',
        'last_name' => 'Mwansa',
        'name' => 'Chanda Mwansa',
        'email' => 'lskbusiness@gmail.com',
        'contact_number' => '0970000000',
        'province_id' => $province->id,
        'district_id' => $district->id,
        'password' => 'Password123!',
    ], $overrides);
}

test('api registration rejects blocked placeholder name john doe', function () {
    $payload = antiFraudRegistrationPayload([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'name' => 'John Doe',
        'email' => 'john.valid@gmail.com',
    ]);

    $this->postJson('/api/register', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('api registration rejects blocked placeholder name test user', function () {
    $payload = antiFraudRegistrationPayload([
        'first_name' => 'Test',
        'last_name' => 'User',
        'name' => 'Test User',
        'email' => 'test.user.valid@gmail.com',
    ]);

    $this->postJson('/api/register', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('api registration rejects blocked placeholder domain example.com', function () {
    $payload = antiFraudRegistrationPayload([
        'email' => 'user@example.com',
    ]);

    $this->postJson('/api/register', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('api registration rejects placeholder local part patterns even on allowed domains', function () {
    $payload = antiFraudRegistrationPayload([
        'email' => 'test123@gmail.com',
    ]);

    $this->postJson('/api/register', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('api registration accepts chanda mwansa', function () {
    $payload = antiFraudRegistrationPayload([
        'name' => 'Chanda Mwansa',
        'first_name' => 'Chanda',
        'last_name' => 'Mwansa',
        'email' => 'chanda.mwansa@gmail.com',
    ]);

    $this->postJson('/api/register', $payload)
        ->assertStatus(201)
        ->assertJsonPath('success', true);
});

test('api registration accepts lskbusiness gmail address', function () {
    $payload = antiFraudRegistrationPayload([
        'email' => 'lskbusiness@gmail.com',
    ]);

    $this->postJson('/api/register', $payload)
        ->assertStatus(201)
        ->assertJsonPath('success', true);
});

test('api registration accepts a real custom domain with valid dns', function () {
    if (! (checkdnsrr('openai.com', 'MX') || checkdnsrr('openai.com', 'A'))) {
        $this->markTestSkipped('DNS is unavailable in this environment.');
    }

    $payload = antiFraudRegistrationPayload([
        'email' => 'contact@openai.com',
    ]);

    $this->postJson('/api/register', $payload)
        ->assertStatus(201)
        ->assertJsonPath('success', true);
});

test('api profile update allows keeping the same email and validates name/email rules', function () {
    $user = User::factory()->create([
        'first_name' => 'Chanda',
        'last_name' => 'Mwansa',
        'email' => 'chanda.keep@gmail.com',
    ]);

    Sanctum::actingAs($user);

    $this->patchJson('/api/me', [
        'first_name' => 'Chanda',
        'last_name' => 'Mwansa',
        'email' => 'chanda.keep@gmail.com',
        'name' => 'Chanda Mwansa',
    ])->assertOk()
      ->assertJsonPath('success', true);
});
