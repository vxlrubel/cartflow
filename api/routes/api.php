<?php

use App\Http\Controllers\API\ActivityLogController;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\AnalyticsController;
use App\Http\Controllers\API\AttributeController;
use App\Http\Controllers\API\AuditController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\OfferController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Debug route
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working']);
    });

    // Auth
    Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Users (Admin)
        Route::apiResource('users', UserController::class);
        Route::post('/users/{id}/restore', [UserController::class, 'restore']);
        Route::delete('/users/{id}/force', [UserController::class, 'forceDelete']);

        // Roles & Permissions
        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::get('/roles/{id}', [RoleController::class, 'show']);
        Route::post('/roles/{id}/assign-permission', [RoleController::class, 'assignPermission']);

        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::post('/permissions', [PermissionController::class, 'store']);

        // Products
        Route::apiResource('products', ProductController::class);
        Route::post('/products/{id}/restore', [ProductController::class, 'restore']);
        Route::delete('/products/{id}/force', [ProductController::class, 'forceDelete']);
        Route::get('/trash/products', [ProductController::class, 'trash']);

        // Categories
        Route::apiResource('categories', CategoryController::class);
        Route::post('/categories/{id}/restore', [CategoryController::class, 'restore']);

        // Brands
        Route::apiResource('brands', BrandController::class);

        // Attributes & Variations
        Route::get('/attributes', [AttributeController::class, 'index']);
        Route::post('/attributes', [AttributeController::class, 'store']);
        Route::post('/attributes/{id}/values', [AttributeController::class, 'addValue']);
        Route::post('/products/{productId}/variations', [AttributeController::class, 'storeVariation']);
        Route::put('/variations/{id}', [AttributeController::class, 'updateVariation']);
        Route::delete('/variations/{id}', [AttributeController::class, 'destroyVariation']);

        // Orders
        Route::apiResource('orders', OrderController::class);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::get('/trash/orders', [OrderController::class, 'trash']);
        Route::post('/trash/orders/{id}/restore', [OrderController::class, 'restore']);

        // Payments
        Route::apiResource('payments', PaymentController::class);

        // Coupons
        Route::apiResource('coupons', CouponController::class);
        Route::post('/coupons/apply', [CouponController::class, 'apply']);

        // Offers
        Route::apiResource('offers', OfferController::class);

        // Analytics & Reports
        Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
        Route::get('/analytics/revenue', [AnalyticsController::class, 'revenue']);
        Route::get('/analytics/products', [AnalyticsController::class, 'products']);

        Route::get('/reports/sales', [AnalyticsController::class, 'sales']);
        Route::get('/reports/orders', [AnalyticsController::class, 'orders']);
        Route::get('/reports/customers', [AnalyticsController::class, 'customers']);

        // Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store']);
        Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

        // Addresses
        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{id}', [AddressController::class, 'update']);
        Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);

        // Audits
        Route::get('/audits', [AuditController::class, 'index']);
    });
});
