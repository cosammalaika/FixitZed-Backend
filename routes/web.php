<?php

use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\FileController;
use App\Livewire\Audit\LoginAuditIndex;
use App\Livewire\Coupon\{CouponCreate, CouponEdit, CouponIndex, CouponShow};
use App\Livewire\Dashboard;
use App\Livewire\Earning\{EarningCreate, EarningEdit, EarningIndex, EarningShow};
use App\Livewire\Fixer\{ApplicationIndex, FixerCreate, FixerEdit, FixerIndex, FixerShow};
use App\Livewire\Location\{LocationCreate, LocationEdit, LocationIndex, LocationShow};
use App\Livewire\Notification\{NotificationCreate, NotificationEdit, NotificationIndex, NotificationShow};
use App\Livewire\LocationOption\LocationOptionIndex;
use App\Livewire\Reportd;
use App\Livewire\Issues;
use App\Livewire\Payment\{PaymentCreate, PaymentEdit, PaymentIndex, PaymentShow};
use App\Livewire\PaymentMethod\PaymentMethodIndex;
use App\Livewire\Rating\{RatingCreate, RatingEdit, RatingIndex, RatingShow};
use App\Livewire\Review\{ReviewCreate, ReviewEdit, ReviewIndex, ReviewShow};
use App\Livewire\Role\{RoleCreate, RoleEdit, RoleIndex, RoleShow};
use App\Livewire\Service\{ServiceCreate, ServiceEdit, ServiceIndex, ServiceShow};
use App\Livewire\ServiceRequest\{ServiceRequestCreate, ServiceRequestEdit, ServiceRequestIndex, ServiceRequestShow};
use App\Livewire\Subscription\{PlanIndex as SubscriptionPlanIndex, SubscriptionIndex as SubscriptionPurchaseIndex};
use App\Livewire\Wallet\WalletIndex as WalletIndex;
use App\Livewire\UserLog\UserLogIndex;
use App\Livewire\Users\{UserCreate, UserEdit, UserIndex, UserShow};
use App\Livewire\Settings\GeneralSettings;
use App\Http\Controllers\Auth\LockController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
Route::middleware(['auth'])
    ->get('/dashboard', Dashboard::class)
    ->name('dashboard');

Route::redirect('/', '/dashboard');
Route::middleware(['auth'])->group(function () {
    Route::get('files/{path}', [FileController::class, 'show'])
        ->where('path', '.*')
        ->name('files.show');

    Route::redirect('settings', 'settings/profile');

    Route::get('reportd', Reportd::class)
        ->name('reportd.index')
        ->middleware('permission:view.reports');

    Route::get('issues', Issues::class)
        ->name('issues.index')
        ->middleware('permission:view.reports');

    Route::get('lock', [LockController::class, 'activate'])->name('lock.activate');
    Route::get('lock-screen', [LockController::class, 'show'])->name('lock.screen');
    Route::post('lock-screen', [LockController::class, 'unlock'])->name('lock.unlock');

    Route::get('logs', UserLogIndex::class)
        ->name('logs.index')
        ->middleware('permission:view.logs');

    Route::get('security/login-audits', LoginAuditIndex::class)
        ->name('audit.login')
        ->middleware('permission:view.logs');

    Route::get('settings/general', GeneralSettings::class)
        ->name('settings.general')
        ->middleware('permission:edit.settings');

    Route::get('users', UserIndex::class)
        ->name('users.index')
        ->middleware('permission:view.users');
    Route::get('users/create', UserCreate::class)
        ->name('users.create')
        ->middleware('permission:create.users');
    Route::get('users/{id}/edit', UserEdit::class)
        ->name('users.edit')
        ->middleware('permission:edit.users');
    Route::get('users/{id}', UserShow::class)
        ->name('users.show')
        ->middleware('permission:show.users');

    Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy'])
        ->name('admin.users.destroy')
        ->middleware(['role:Super Admin', 'permission:delete.users']);

    Route::get('services', ServiceIndex::class)
        ->name('services.index')
        ->middleware('permission:view.services');
    Route::get('services/create', ServiceCreate::class)
        ->name('services.create')
        ->middleware('permission:create.services');
    Route::get('services/{id}/edit', ServiceEdit::class)
        ->name('services.edit')
        ->middleware('permission:edit.services');
    Route::get('services/{id}', ServiceShow::class)
        ->name('services.show')
        ->middleware('permission:show.services');

    Route::get('role', RoleIndex::class)
        ->name('role.index')
        ->middleware('permission:view.roles');
    Route::get('role/create', RoleCreate::class)
        ->name('roles.create')
        ->middleware('permission:create.roles');
    Route::get('role/{id}/edit', RoleEdit::class)
        ->name('roles.edit')
        ->middleware('permission:edit.roles');
    Route::get('role/{id}', RoleShow::class)
        ->name('roles.show')
        ->middleware('permission:show.roles');

    Route::get('coupon', CouponIndex::class)
        ->name('coupon.index')
        ->middleware('permission:view.coupons');
    Route::get('coupon/create', CouponCreate::class)
        ->name('coupon.create')
        ->middleware('permission:create.coupons');
    Route::get('coupon/{id}/edit', CouponEdit::class)
        ->name('coupon.edit')
        ->middleware('permission:edit.coupons');
    Route::get('coupon/{id}', CouponShow::class)
        ->name('coupon.show')
        ->middleware('permission:show.coupons');

    Route::get('earning', EarningIndex::class)
        ->name('earning.index')
        ->middleware('permission:view.earnings');
    Route::get('earning/create', EarningCreate::class)
        ->name('earning.create')
        ->middleware('permission:create.earnings');
    Route::get('earning/{id}/edit', EarningEdit::class)
        ->name('earning.edit')
        ->middleware('permission:edit.earnings');
    Route::get('earning/{id}', EarningShow::class)
        ->name('earning.show')
        ->middleware('permission:show.earnings');

    Route::get('fixer', FixerIndex::class)
        ->name('fixer.index')
        ->middleware('permission:view.fixers');
    Route::get('fixer/applications', ApplicationIndex::class)
        ->name('fixer.applications')
        ->middleware('permission:view.fixers');
    Route::get('fixer/create', FixerCreate::class)
        ->name('fixer.create')
        ->middleware('permission:create.fixers');
    Route::get('fixer/{id}/edit', FixerEdit::class)
        ->name('fixer.edit')
        ->middleware('permission:edit.fixers');
    Route::get('fixer/{id}', FixerShow::class)
        ->name('fixer.show')
        ->middleware('permission:show.fixers');

    Route::get('location', LocationIndex::class)
        ->name('location.index')
        ->middleware('permission:view.locations');
    Route::get('location/create', LocationCreate::class)
        ->name('location.create')
        ->middleware('permission:create.locations');
    Route::get('location/{id}/edit', LocationEdit::class)
        ->name('location.edit')
        ->middleware('permission:edit.locations');
    Route::get('location/{id}', LocationShow::class)
        ->name('location.show')
        ->middleware('permission:show.locations');

    Route::get('notification', NotificationIndex::class)
        ->name('notification.index')
        ->middleware('permission:view.notifications');
    Route::get('notification/create', NotificationCreate::class)
        ->name('notification.create')
        ->middleware('permission:create.notifications');
    Route::get('notification/{id}/edit', NotificationEdit::class)
        ->name('notification.edit')
        ->middleware('permission:edit.notifications');
    Route::get('notification/{id}', NotificationShow::class)
        ->name('notification.show')
        ->middleware('permission:show.notifications');

    Route::post('admin/notifications/bulk-delete', [AdminNotificationController::class, 'bulkDelete'])
        ->name('admin.notifications.bulkDelete')
        ->middleware(['role:Super Admin|Admin']);

    Route::get('location-options', LocationOptionIndex::class)
        ->name('location-options.index')
        ->middleware('permission:view.location_options');

    Route::get('payment', PaymentIndex::class)
        ->name('payment.index')
        ->middleware('permission:view.payments');
    Route::get('payment/create', PaymentCreate::class)
        ->name('payment.create')
        ->middleware('permission:create.payments');
    Route::get('payment/{id}/edit', PaymentEdit::class)
        ->name('payment.edit')
        ->middleware('permission:edit.payments');
    Route::get('payment/{id}', PaymentShow::class)
        ->name('payment.show')
        ->middleware('permission:show.payments');

    Route::get('payment-methods', PaymentMethodIndex::class)
        ->name('payment-methods.index')
        ->middleware('permission:view.payment_methods');

    Route::get('rating', RatingIndex::class)
        ->name('rating.index')
        ->middleware('permission:view.ratings');
    Route::get('rating/create', RatingCreate::class)
        ->name('rating.create')
        ->middleware('permission:create.ratings');
    Route::get('rating/{id}/edit', RatingEdit::class)
        ->name('rating.edit')
        ->middleware('permission:edit.ratings');
    Route::get('rating/{id}', RatingShow::class)
        ->name('rating.show')
        ->middleware('permission:show.ratings');

    Route::get('review', ReviewIndex::class)
        ->name('review.index')
        ->middleware('permission:view.reviews');
    Route::get('review/create', ReviewCreate::class)
        ->name('review.create')
        ->middleware('permission:create.reviews');
    Route::get('review/{id}/edit', ReviewEdit::class)
        ->name('review.edit')
        ->middleware('permission:edit.reviews');
    Route::get('review/{id}', ReviewShow::class)
        ->name('review.show')
        ->middleware('permission:show.reviews');

    Route::get('subscriptions/plans', SubscriptionPlanIndex::class)
        ->name('subscriptions.plans')
        ->middleware('permission:view.subscriptions');
    Route::get('subscriptions/purchases', SubscriptionPurchaseIndex::class)
        ->name('subscriptions.purchases')
        ->middleware('permission:view.subscriptions');
    Route::get('wallets', WalletIndex::class)
        ->name('wallet.index')
        ->middleware('permission:view.wallet');

    Route::get('serviceRequest', ServiceRequestIndex::class)
        ->name('serviceRequest.index')
        ->middleware('permission:view.service_requests');
    Route::get('serviceRequest/create', ServiceRequestCreate::class)
        ->name('serviceRequest.create')
        ->middleware('permission:create.service_requests');
    Route::get('serviceRequest/{id}/edit', ServiceRequestEdit::class)
        ->name('serviceRequest.edit')
        ->middleware('permission:edit.service_requests');
    Route::get('serviceRequest/{id}', ServiceRequestShow::class)
        ->name('serviceRequest.show')
        ->middleware('permission:show.service_requests');


    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
