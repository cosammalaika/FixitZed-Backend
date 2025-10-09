<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-subscriptions', function ($user) {
            if (! $user) {
                return false;
            }

            $permissions = ['manage.subscriptions', 'view.subscriptions'];
            foreach ($permissions as $permission) {
                if (method_exists($user, 'hasPermissionTo')) {
                    try {
                        if ($user->hasPermissionTo($permission)) {
                            return true;
                        }
                    } catch (\Throwable $e) {
                        // Permission may not be registered yet; fall back to other strategies.
                    }
                }

                if (method_exists($user, 'can') && $user->can($permission)) {
                    return true;
                }
            }

            if (method_exists($user, 'hasRole') && $user->hasRole(['Super Admin', 'Admin', 'Support'])) {
                return true;
            }

            return false;
        });
    }
}
