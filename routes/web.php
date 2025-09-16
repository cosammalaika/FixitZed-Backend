<?php

use App\Livewire\Category\{CategoryCreate, CategoryEdit, CategoryIndex, CategoryShow};
use App\Livewire\Coupon\{CouponCreate, CouponEdit, CouponIndex, CouponShow};
use App\Livewire\Dashboard;
use App\Livewire\Earning\{EarningCreate, EarningEdit, EarningIndex, EarningShow};
use App\Livewire\Fixer\{FixerCreate, FixerEdit, FixerIndex, FixerShow};
use App\Livewire\Location\{LocationCreate, LocationEdit, LocationIndex, LocationShow};
use App\Livewire\Notification\{NotificationCreate, NotificationEdit, NotificationIndex, NotificationShow};
use App\Livewire\LocationOption\LocationOptionIndex;
use App\Livewire\Payment\{PaymentCreate, PaymentEdit, PaymentIndex, PaymentShow};
use App\Livewire\Rating\{RatingCreate, RatingEdit, RatingIndex, RatingShow};
use App\Livewire\Review\{ReviewCreate, ReviewEdit, ReviewIndex, ReviewShow};
use App\Livewire\Role\{RoleCreate, RoleEdit, RoleIndex, RoleShow};
use App\Livewire\Service\{ServiceCreate, ServiceEdit, ServiceIndex, ServiceShow};
use App\Livewire\ServiceRequest\{ServiceRequestCreate, ServiceRequestEdit, ServiceRequestIndex, ServiceRequestShow};
use App\Livewire\Subcategory\{SubcategoryCreate, SubcategoryEdit, SubcategoryIndex, SubcategoryShow};
use App\Livewire\UserLog\UserLogIndex;
use App\Livewire\Users\{UserCreate, UserEdit, UserIndex, UserShow};
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
Route::middleware(['auth', 'verified'])
    ->get('/dashboard', Dashboard::class)
    ->name('dashboard');

Route::redirect('/', '/dashboard');
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('logs', UserLogIndex::class)->name('logs.index');

    Route::get('users', UserIndex::class)->name('users.index');
    Route::get('users/create', UserCreate::class)->name('users.create');
    Route::get('users/{id}/edit', UserEdit::class)->name('users.edit');
    Route::get('users/{id}', UserShow::class)->name('users.show');

    // ->middleware("permission:view.services|create.services|edit.services|show.services|delete.services")
    Route::get('services', ServiceIndex::class)->name('services.index');
    Route::get('services/create', ServiceCreate::class)->name('services.create');
    Route::get('services/{id}/edit', ServiceEdit::class)->name('services.edit');
    Route::get('services/{id}', ServiceShow::class)->name('services.show');

    Route::get('role', RoleIndex::class)->name('role.index');
    Route::get('role/create', RoleCreate::class)->name(name: 'roles.create');
    Route::get('role/{id}/edit', RoleEdit::class)->name('roles.edit');
    Route::get('role/{id}', RoleShow::class)->name('roles.show');

    
    Route::get('category', CategoryIndex::class)->name('category.index');
    Route::get('category/create', CategoryCreate::class)->name(name: 'category.create');
    Route::get('category/{id}/edit', CategoryEdit::class)->name('category.edit');
    Route::get('category/{id}', CategoryShow::class)->name('category.show');

    Route::get('coupon', CouponIndex::class)->name('coupon.index');
    Route::get('coupon/create', CouponCreate::class)->name(name: 'coupon.create');
    Route::get('coupon/{id}/edit', CouponEdit::class)->name('coupon.edit');
    Route::get('coupon/{id}', CouponShow::class)->name('coupon.show');

    Route::get('earning', EarningIndex::class)->name('earning.index');
    Route::get('earning/create', EarningCreate::class)->name(name: 'earning.create');
    Route::get('earning/{id}/edit', EarningEdit::class)->name('earning.edit');
    Route::get('earning/{id}', EarningShow::class)->name('earning.show');

    Route::get('fixer', FixerIndex::class)->name('fixer.index');
    Route::get('fixer/create', FixerCreate::class)->name(name: 'fixer.create');
    Route::get('fixer/{id}/edit', FixerEdit::class)->name('fixer.edit');
    Route::get('fixer/{id}', FixerShow::class)->name('fixer.show');

    Route::get('location', LocationIndex::class)->name('location.index');
    Route::get('location/create', LocationCreate::class)->name(name: 'location.create');
    Route::get('location/{id}/edit', LocationEdit::class)->name('location.edit');
    Route::get('location/{id}', LocationShow::class)->name('location.show');

    Route::get('notification', NotificationIndex::class)->name('notification.index');
    Route::get('notification/create', NotificationCreate::class)->name(name: 'notification.create');
    Route::get('notification/{id}/edit', NotificationEdit::class)->name('notification.edit');
    Route::get('notification/{id}', NotificationShow::class)->name('notification.show');

    // Managed Locations (Location Options)
    Route::get('location-options', LocationOptionIndex::class)->name('location-options.index');

    Route::get('payment', PaymentIndex::class)->name('payment.index');
    Route::get('payment/create', PaymentCreate::class)->name(name: 'payment.create');
    Route::get('payment/{id}/edit', PaymentEdit::class)->name('payment.edit');
    Route::get('payment/{id}', PaymentShow::class)->name('payment.show');

    Route::get('rating', RatingIndex::class)->name('rating.index');
    Route::get('rating/create', RatingCreate::class)->name(name: 'rating.create');
    Route::get('rating/{id}/edit', RatingEdit::class)->name('rating.edit');
    Route::get('rating/{id}', RatingShow::class)->name('rating.show');

    Route::get('review', ReviewIndex::class)->name('review.index');
    Route::get('review/create', ReviewCreate::class)->name(name: 'review.create');
    Route::get('review/{id}/edit', ReviewEdit::class)->name('review.edit');
    Route::get('review/{id}', ReviewShow::class)->name('review.show');

    Route::get('subcategory', action: SubcategoryIndex::class)->name('subcategory.index');
    Route::get('subcategory/create', SubcategoryCreate::class)->name(name: 'subcategory.create');
    Route::get('subcategory/{id}/edit', SubcategoryEdit::class)->name('subcategory.edit');
    Route::get('subcategory/{id}', SubcategoryShow::class)->name('subcategory.show');

    Route::get('serviceRequest', action: ServiceRequestIndex::class)->name('serviceRequest.index');
    Route::get('serviceRequest/create', ServiceRequestCreate::class)->name(name: 'serviceRequest.create');
    Route::get('serviceRequest/{id}/edit', ServiceRequestEdit::class)->name('serviceRequest.edit');
    Route::get('serviceRequest/{id}', ServiceRequestShow::class)->name('serviceRequest.show');


    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__ . '/auth.php';
