<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Fixer;
use App\Models\Service;
use App\Models\Subcategory;
use App\Support\ApiCache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Component as LivewireComponent;
use Livewire\Features\SupportEvents\Event as LivewireEvent;
use function Livewire\store;

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
        if (! LivewireComponent::hasMacro('dispatchBrowserEvent')) {
            LivewireComponent::macro('dispatchBrowserEvent', function (string $event, $data = []) {
                $payload = is_array($data) ? $data : [$data];

                store($this)->push('dispatched', new LivewireEvent($event, $payload));

                return $this;
            });
        }

        Schema::defaultStringLength(191);

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

        if (ApiCache::enabled()) {
            Category::saved(fn () => ApiCache::flush(['catalog', 'categories']));
            Category::deleted(fn () => ApiCache::flush(['catalog', 'categories']));

            Subcategory::saved(fn () => ApiCache::flush(['catalog', 'subcategories']));
            Subcategory::deleted(fn () => ApiCache::flush(['catalog', 'subcategories']));

            Service::saved(fn () => ApiCache::flush(['catalog', 'services']));
            Service::deleted(fn () => ApiCache::flush(['catalog', 'services']));

            Fixer::saved(function (Fixer $fixer) {
                ApiCache::flush(['fixers', 'fixers:top', 'user:' . $fixer->user_id]);
            });
            Fixer::deleted(function (Fixer $fixer) {
                ApiCache::flush(['fixers', 'fixers:top', 'user:' . $fixer->user_id]);
            });
        }
    }
}
