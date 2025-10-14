<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        $customer = User::firstOrCreate([
            'email' => 'cosammalaika@example.com',
        ], [
            'first_name' => 'Cosam',
            'last_name' => 'Malaika',
            'username' => 'AngelOnIt',
            'contact_number' => '0970000000',
            'status' => 'Active',
            'password' => Hash::make('password'),
        ]);
        $customer->assignRole('Customer');

        $adminUser = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'contact_number' => '0970000000',
            'status' => 'Active',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole('Super Admin');

        $supportUser = User::firstOrCreate([
            'email' => 'support@example.com',
        ], [
            'first_name' => 'Support',
            'last_name' => 'Agent',
            'username' => 'support',
            'contact_number' => '0960000000',
            'status' => 'Active',
            'password' => Hash::make('password'),
        ]);
        $supportUser->assignRole('Support');

        $this->call([
            LocationOptionSeeder::class,
            ServiceCatalogSeeder::class,
            ZambianUsersSeeder::class,
            CouponSeeder::class,
        ]);

        // Ensure every user has at least the Customer role
        User::doesntHave('roles')->each(function (User $user) {
            $user->assignRole('Customer');
        });
    }
}
