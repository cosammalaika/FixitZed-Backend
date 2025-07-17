<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $permissions = [
            // Users
            'view.users',
            'create.users',
            'edit.users',
            'show.users',
            'delete.users',

            // Services
            'view.services',
            'create.services',
            'edit.services',
            'show.services',
            'delete.services',

            // Roles
            'view.roles',
            'create.roles',
            'show.roles',
            'edit.roles',
            'delete.roles',

            // Any other entities like orders, reports, etc.
        ];
         foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('Permissions seeded successfully.');
    }
}
