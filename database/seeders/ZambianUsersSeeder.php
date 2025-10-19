<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ZambianUsersSeeder extends Seeder
{
    public function run(): void
    {
        $regionalAdmin = User::updateOrCreate([
            'email' => 'admin.zm@example.com',
        ], [
            'first_name' => 'Chanda',
            'last_name' => 'Mulenga',
            'username' => 'admin_zm',
            'contact_number' => '0970000001',
            'status' => 'Active',
            'province' => 'Lusaka',
            'district' => 'Lusaka CBD',
            'address' => 'Lusaka, Lusaka CBD',
            'password' => Hash::make('password'),
        ]);
        $regionalAdmin->assignRole('Customer');

        $regionalSupport = User::updateOrCreate([
            'email' => 'support.zm@example.com',
        ], [
            'first_name' => 'Thandiwe',
            'last_name' => 'Phiri',
            'username' => 'support_zm',
            'contact_number' => '0960000001',
            'status' => 'Active',
            'province' => 'Lusaka',
            'district' => 'Lusaka CBD',
            'address' => 'Lusaka, Lusaka CBD',
            'password' => Hash::make('password'),
        ]);
        $regionalSupport->assignRole('Customer');
    }
}
