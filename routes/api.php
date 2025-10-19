<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\FixerController;
use App\Http\Controllers\Api\SubcategoryController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\LocationOptionController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\FixerRequestController;
use App\Http\Controllers\Api\LoyaltyController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\EarningController;

// Guest routes (no authentication required)
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('password/reset', [AuthController::class, 'resetPassword']);

// Public data (read-only)
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('categories/{category}/subcategories', [CategoryController::class, 'subcategories']);
Route::get('services', [ServiceController::class, 'index']);
Route::get('services/{service}', [ServiceController::class, 'show']);
Route::get('subcategories', [SubcategoryController::class, 'index']);
Route::get('subcategories/{subcategory}', [SubcategoryController::class, 'show']);
Route::get('fixers', [FixerController::class, 'index']);
Route::get('fixers/top', [FixerController::class, 'top']);
Route::get('fixers/{fixer}', [FixerController::class, 'show']);
Route::get('services/{service}/reviews', [ReviewController::class, 'index']);
Route::get('coupons', [CouponController::class, 'index']);
Route::get('coupons/{coupon}', [CouponController::class, 'show']);
Route::post('coupons/validate', [CouponController::class, 'validateCode']);
Route::get('payment-methods', [PaymentMethodController::class, 'index']);
Route::get('currency', [CurrencyController::class, 'show']);
Route::get('provinces', [ProvinceController::class, 'index']);
// Location options for dropdown
Route::get('location-options', [LocationOptionController::class, 'index']);

// Subscription plans (public)
Route::get('subscription/plans', [SubscriptionController::class, 'plans']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::patch('me', [AuthController::class, 'updateMe']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Account security
    Route::post('password', [AuthController::class, 'changePassword']);
    Route::patch('me/password', [AuthController::class, 'changePassword']);

    // Service Requests for the authenticated user
    Route::get('requests', [ServiceRequestController::class, 'index']);
    Route::post('requests', [ServiceRequestController::class, 'store']);
    Route::get('requests/{serviceRequest}', [ServiceRequestController::class, 'show']);
    Route::patch('requests/{serviceRequest}', [ServiceRequestController::class, 'update']);
    Route::post('requests/{serviceRequest}/cancel', [ServiceRequestController::class, 'cancel']);

    // Locations (for current user)
    Route::get('locations', [LocationController::class, 'index']);
    Route::post('locations', [LocationController::class, 'store']);
    Route::patch('locations/{location}', [LocationController::class, 'update']);
    Route::delete('locations/{location}', [LocationController::class, 'destroy']);

    // Manage location options (admin-capable)
    Route::post('location-options', [LocationOptionController::class, 'store']);
    Route::patch('location-options/{locationOption}', [LocationOptionController::class, 'update']);
    Route::patch('location-options/{locationOption}/toggle', [LocationOptionController::class, 'toggle']);
    Route::delete('location-options/{locationOption}', [LocationOptionController::class, 'destroy']);

    // Reviews and ratings
    Route::post('services/{service}/reviews', [ReviewController::class, 'store']);
    Route::post('service-requests/{serviceRequest}/ratings', [RatingController::class, 'store']);
    Route::get('users/{user}/ratings', [RatingController::class, 'listForUser']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Payments
    Route::get('requests/{serviceRequest}/payment', [PaymentController::class, 'show']);
    Route::post('requests/{serviceRequest}/payment', [PaymentController::class, 'store']);

    // Loyalty summary
    Route::get('loyalty', [LoyaltyController::class, 'show']);

    // Reports
    Route::post('reports', [ReportController::class, 'store']);
    Route::get('reports', [ReportController::class, 'index']); // admin only
    Route::patch('reports/{report}', [ReportController::class, 'update']); // admin only

    // Fixer wallet and subscriptions
    Route::get('fixer/wallet', [SubscriptionController::class, 'myWallet']);
    Route::get('fixer/earnings/history', [EarningController::class, 'history']);
    Route::post('subscription/checkout', [SubscriptionController::class, 'checkout']);
    Route::post('subscription/webhook', [SubscriptionController::class, 'webhook']);

    // Fixer profile management
    Route::get('fixer/me', [FixerController::class, 'current']);
    Route::patch('fixer/me', [FixerController::class, 'update']);

    // Fixer requests
    Route::get('fixer/requests', [FixerRequestController::class, 'index']);
    Route::post('service-requests/{serviceRequest}/accept', [FixerRequestController::class, 'accept']);
    // Fixer creates a bill for a request they own
    Route::post('fixer/requests/{serviceRequest}/bill', [FixerRequestController::class, 'bill']);
    Route::post('fixer/requests/{serviceRequest}/decline', [FixerRequestController::class, 'decline']);
    Route::post('fixer/requests/{serviceRequest}/snooze', [FixerRequestController::class, 'snooze']);
    // Customer applies to become a fixer
    Route::post('fixer/apply', [FixerController::class, 'apply']);
});
