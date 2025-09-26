<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

       User::factory()->create([
            'first_name' => 'Cosam',
            'last_name' => 'Malaika',
            'username' => 'AngelOnIt',
            'email' => 'cosammalaika@example.com',
            'contact_number' => '0970000000',
            'user_type' => 'Customer',
            'password' => bcrypt('password'), 
        ]);

        // Additional named users
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'user_type' => 'Admin',
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'first_name' => 'Support',
            'last_name' => 'Agent',
            'username' => 'support',
            'email' => 'support@example.com',
            'user_type' => 'Support',
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        // Bulk customers
        User::factory(15)->create([
            'user_type' => 'Customer',
            'status' => 'Active',
        ]);

        // Seed default location options
        $this->call(LocationOptionSeeder::class);

        // Seed service catalog (categories, subcategories, services)
        $this->call(ServiceCatalogSeeder::class);

        // Seed Zambian users
        $this->call(ZambianUsersSeeder::class);

        // Seed coupons (Zambia-tailored)
        $this->call(CouponSeeder::class);
    }
}
