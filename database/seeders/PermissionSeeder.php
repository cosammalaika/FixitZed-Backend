<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard' => ['view'],
            'users' => ['view', 'create', 'show', 'edit', 'delete'],
            'roles' => ['view', 'create', 'show', 'edit', 'delete'],
            'permissions' => ['view', 'assign'],
            'services' => ['view', 'create', 'show', 'edit', 'delete'],
            'service_requests' => ['view', 'create', 'show', 'edit', 'delete', 'assign', 'reassign'],
            'fixers' => ['view', 'create', 'show', 'edit', 'delete'],
            'customers' => ['view', 'create', 'show', 'edit', 'delete'],
            'coupons' => ['view', 'create', 'show', 'edit', 'delete'],
            'payments' => ['view', 'create', 'show', 'edit', 'delete', 'refund', 'export'],
            'payment_methods' => ['view', 'create', 'edit', 'delete'],
            'earnings' => ['view', 'create', 'show', 'edit', 'delete', 'export'],
            'wallet' => ['view', 'adjust'],
            'subscriptions' => ['view', 'create', 'show', 'edit', 'delete'],
            'notifications' => ['view', 'create', 'show', 'edit', 'delete', 'send'],
            'reports' => ['view', 'resolve'],
            'ratings' => ['view', 'create', 'show', 'edit', 'delete'],
            'reviews' => ['view', 'create', 'show', 'edit', 'delete'],
            'locations' => ['view', 'create', 'show', 'edit', 'delete'],
            'location_options' => ['view', 'edit'],
            'loyalty' => ['view', 'edit'],
            'logs' => ['view'],
            'settings' => ['view', 'edit'],
        ];

        $permissionIndex = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $name = sprintf('%s.%s', $action, $module);
                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]);
                $permissionIndex[$module][$action] = $name;
            }
        }

        $allPermissions = collect($permissionIndex)
            ->flatMap(fn ($actions) => array_values($actions))
            ->unique()
            ->values()
            ->all();

        $resolve = function (array $map) use ($permissionIndex) {
            $names = [];
            foreach ($map as $module => $actions) {
                foreach ($actions as $action) {
                    if (isset($permissionIndex[$module][$action])) {
                        $names[] = $permissionIndex[$module][$action];
                    }
                }
            }
            return array_values(array_unique($names));
        };

        $roleMatrix = [
            'Super Admin' => $allPermissions,
            'Admin' => $resolve([
                'dashboard' => ['view'],
                'users' => ['view', 'create', 'show', 'edit', 'delete'],
                'roles' => ['view', 'create', 'show', 'edit', 'delete'],
                'permissions' => ['view'],
                'services' => ['view', 'create', 'show', 'edit', 'delete'],
                'service_requests' => ['view', 'create', 'show', 'edit', 'delete', 'assign', 'reassign'],
                'fixers' => ['view', 'create', 'show', 'edit', 'delete'],
                'customers' => ['view', 'create', 'show', 'edit', 'delete'],
                'coupons' => ['view', 'create', 'show', 'edit', 'delete'],
                'payments' => ['view', 'create', 'show', 'edit', 'delete', 'refund', 'export'],
                'payment_methods' => ['view', 'create', 'edit', 'delete'],
                'earnings' => ['view', 'create', 'show', 'edit', 'delete', 'export'],
                'wallet' => ['view', 'adjust'],
                'subscriptions' => ['view', 'create', 'show', 'edit', 'delete'],
                'notifications' => ['view', 'create', 'show', 'edit', 'delete', 'send'],
                'reports' => ['view', 'resolve'],
                'ratings' => ['view', 'create', 'show', 'edit', 'delete'],
                'reviews' => ['view', 'create', 'show', 'edit', 'delete'],
                'locations' => ['view', 'create', 'show', 'edit', 'delete'],
                'location_options' => ['view', 'edit'],
                'loyalty' => ['view', 'edit'],
                'logs' => ['view'],
                'settings' => ['view', 'edit'],
            ]),
            'Support' => $resolve([
                'dashboard' => ['view'],
                'users' => ['view', 'show'],
                'service_requests' => ['view', 'show', 'edit', 'assign'],
                'fixers' => ['view', 'show'],
                'customers' => ['view', 'show'],
                'coupons' => ['view', 'create', 'show', 'edit'],
                'payments' => ['view', 'show'],
                'earnings' => ['view'],
                'notifications' => ['view', 'create', 'show', 'edit'],
                'reports' => ['view', 'resolve'],
            ]),
            'Fixer' => $resolve([
                'dashboard' => ['view'],
                'service_requests' => ['view', 'show', 'edit'],
                'payments' => ['view', 'show'],
                'earnings' => ['view'],
                'notifications' => ['view', 'create'],
                'wallet' => ['view'],
            ]),
            'Customer' => $resolve([
                'services' => ['view', 'show'],
                'service_requests' => ['view', 'create', 'show'],
                'payments' => ['view', 'create', 'show'],
                'coupons' => ['view', 'show'],
                'notifications' => ['view'],
            ]),
        ];

        foreach ($roleMatrix as $name => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($permissions);
        }

        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin',
                'contact_number' => '0970000000',
                'status' => 'Active',
                'address' => 'Lusaka, Zambia',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdminUser->assignRole('Super Admin');

        $supportUser = User::firstOrCreate(
            ['email' => 'support@example.com'],
            [
                'first_name' => 'Support',
                'last_name' => 'Agent',
                'username' => 'support',
                'contact_number' => '0960000000',
                'status' => 'Active',
                'address' => 'Lusaka, Zambia',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $supportUser->assignRole('Support');

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
