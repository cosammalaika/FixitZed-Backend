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

// Guest routes (no authentication required)
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Public data (read-only)
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('categories/{category}/subcategories', [CategoryController::class, 'subcategories']);
Route::get('services', [ServiceController::class, 'index']);
Route::get('services/{service}', [ServiceController::class, 'show']);
Route::get('subcategories', [SubcategoryController::class, 'index']);
Route::get('subcategories/{subcategory}', [SubcategoryController::class, 'show']);
Route::get('fixers', [FixerController::class, 'index']);
Route::get('fixers/{fixer}', [FixerController::class, 'show']);
Route::get('services/{service}/reviews', [ReviewController::class, 'index']);
Route::post('coupons/validate', [CouponController::class, 'validateCode']);
// Location options for dropdown
Route::get('location-options', [LocationOptionController::class, 'index']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    // Service Requests for the authenticated user
    Route::get('requests', [ServiceRequestController::class, 'index']);
    Route::post('requests', [ServiceRequestController::class, 'store']);
    Route::get('requests/{serviceRequest}', [ServiceRequestController::class, 'show']);
    Route::patch('requests/{serviceRequest}', [ServiceRequestController::class, 'update']);

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
});
