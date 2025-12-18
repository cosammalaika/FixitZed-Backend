<?php

use App\Models\District;
use App\Models\Province;

test('api registration does not 500 with settings throttle middleware', function () {
    $province = Province::create([
        'name' => 'Test Province',
        'slug' => 'test-province',
    ]);

    $district = District::create([
        'province_id' => $province->id,
        'name' => 'Test District',
        'slug' => 'test-district',
    ]);

    $payload = [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test+'.uniqid().'@example.com',
        'contact_number' => '0970000000',
        'province_id' => $province->id,
        'district_id' => $district->id,
        'password' => 'Password123!',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertStatus(201);
    $response->assertJsonPath('success', true);
});

