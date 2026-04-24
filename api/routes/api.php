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
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\CustomerGroupController;
use App\Http\Controllers\API\EmailCampaignController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\ReportsController;
use App\Http\Controllers\API\OfferController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Upload (temp: no auth for testing)
    Route::post('/upload', [UploadController::class, 'upload']);
    Route::delete('/upload', [UploadController::class, 'delete']);

    // Upload (requires auth)
    // Route::middleware('auth:sanctum')->group(function () {
    //     Route::post('/upload', [UploadController::class, 'upload']);
    //     Route::delete('/upload', [UploadController::class, 'delete']);
    // });

    // Debug route
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working']);
    });

    Route::post('/auth/debug', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'received' => $request->all(),
            'headers' => $request->headers->all(),
        ]);
    });

    // Auth
    Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

    Route::post('/auth/test-db', function () {
        $users = \App\Models\User::with('role')->get(['id', 'email', 'name']);
        return response()->json(['users' => $users, 'count' => $users->count()]);
    });

    Route::post('/auth/create-admin', function () {
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin@cartflow.com'],
            ['name' => 'Admin User', 'password' => \Illuminate\Support\Facades\Hash::make('password')]
        );
        return response()->json(['user' => $admin]);
    });

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
        Route::post('/products/bulk-soft-delete', [ProductController::class, 'bulkSoftDelete']);
        Route::post('/products/bulk-active', [ProductController::class, 'bulkActive']);
        Route::post('/products/bulk-inactive', [ProductController::class, 'bulkInactive']);

        // Categories
        Route::apiResource('categories', CategoryController::class);
        Route::post('/categories/{id}/restore', [CategoryController::class, 'restore']);

        // Brands
        Route::apiResource('brands', BrandController::class);

        // Attributes & Variations
        Route::get('/attributes', [AttributeController::class, 'index']);
        Route::post('/attributes', [AttributeController::class, 'store']);
        Route::post('/attributes/{id}/values', [AttributeController::class, 'addValue']);
        Route::get('/variations', [AttributeController::class, 'indexVariations']);
        Route::post('/products/{productId}/variations', [AttributeController::class, 'storeVariation']);
        Route::put('/variations/{id}', [AttributeController::class, 'updateVariation']);
        Route::delete('/variations/{id}', [AttributeController::class, 'destroyVariation']);

        // Reviews
        Route::apiResource('reviews', ReviewController::class);
        Route::post('/reviews/{id}/status', [ReviewController::class, 'updateStatus']);

        // Orders
        Route::apiResource('orders', OrderController::class);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('/orders/{id}/payment-status', [OrderController::class, 'updatePaymentStatus']);
        Route::get('/trash/orders', [OrderController::class, 'trash']);
        Route::post('/trash/orders/{id}/restore', [OrderController::class, 'restore']);
        Route::delete('/trash/orders/{id}/force', [OrderController::class, 'forceDelete']);

        // Payments
        Route::apiResource('payments', PaymentController::class);

        // Coupons
        Route::apiResource('coupons', CouponController::class);
        Route::post('/coupons/apply', [CouponController::class, 'apply']);
        Route::get('/trash/coupons', [CouponController::class, 'trash']);
        Route::post('/coupons/{id}/restore', [CouponController::class, 'restore']);
        Route::delete('/coupons/{id}/force', [CouponController::class, 'forceDelete']);
        Route::get('/coupons/usage', [CouponController::class, 'usage']);

        // Offers
        Route::apiResource('offers', OfferController::class);
        Route::get('/trash/offers', [OfferController::class, 'trash']);
        Route::post('/offers/{id}/restore', [OfferController::class, 'restore']);
        Route::delete('/offers/{id}/force', [OfferController::class, 'forceDelete']);

        // Email Campaigns
        Route::apiResource('email-campaigns', EmailCampaignController::class);
        Route::post('/email-campaigns/{id}/send', [EmailCampaignController::class, 'send']);
        Route::get('/trash/email-campaigns', [EmailCampaignController::class, 'trash']);
        Route::post('/email-campaigns/{id}/restore', [EmailCampaignController::class, 'restore']);
        Route::delete('/email-campaigns/{id}/force', [EmailCampaignController::class, 'forceDelete']);

        // Analytics & Reports (specific routes first to avoid conflicts)
        Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
        Route::get('/analytics/revenue', [AnalyticsController::class, 'revenue']);
        Route::get('/analytics/products', [AnalyticsController::class, 'products']);
        Route::get('/analytics/sales', [AnalyticsController::class, 'sales']);
        Route::get('/analytics/orders', [AnalyticsController::class, 'orders']);
        Route::get('/analytics/customers', [AnalyticsController::class, 'customers']);

        Route::apiResource('analytics', AnalyticsController::class);
        Route::post('/analytics/{id}/restore', [AnalyticsController::class, 'restore']);
        Route::delete('/analytics/{id}/force', [AnalyticsController::class, 'forceDelete']);
        Route::get('/trash/analytics', [AnalyticsController::class, 'trash']);

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

        // Customers
        Route::apiResource('customers', CustomerController::class);
        Route::post('/customers/{id}/restore', [CustomerController::class, 'restore']);
        Route::delete('/customers/{id}/force', [CustomerController::class, 'forceDelete']);
        Route::post('/customers/bulk-soft-delete', [CustomerController::class, 'bulkSoftDelete']);
        Route::post('/customers/bulk-active', [CustomerController::class, 'bulkActive']);
        Route::post('/customers/bulk-inactive', [CustomerController::class, 'bulkInactive']);

        // Customer Groups
        Route::apiResource('customer-groups', CustomerGroupController::class);
        Route::post('/customer-groups/{id}/customers', [CustomerGroupController::class, 'addCustomers']);
        Route::delete('/customer-groups/{id}/customers', [CustomerGroupController::class, 'removeCustomers']);

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);

        // Audits
        Route::get('/audits', [AuditController::class, 'index']);

        // Reports
        Route::get('/reports/period', [ReportsController::class, 'period']);
        Route::get('/reports/revenue', [ReportsController::class, 'revenue']);
        Route::get('/reports/orders', [ReportsController::class, 'orders']);
        Route::get('/reports/taxes', [ReportsController::class, 'taxes']);
        Route::get('/reports/export', [ReportsController::class, 'export']);
        Route::get('/reports/summary', [ReportsController::class, 'summary']);

        // Inventory Management
        Route::get('/inventory', [InventoryController::class, 'index']);
        Route::put('/inventory/{id}', [InventoryController::class, 'update']);
        Route::post('/inventory/bulk-update', [InventoryController::class, 'bulkUpdate']);
        Route::get('/inventory/alerts', [InventoryController::class, 'alerts']);
        Route::post('/inventory/alerts/{id}/dismiss', [InventoryController::class, 'dismissAlert']);

        // SKU Management
        Route::get('/inventory/skus', [InventoryController::class, 'skus']);
        Route::put('/inventory/skus/{id}', [InventoryController::class, 'updateSku']);
        Route::post('/inventory/skus/generate', [InventoryController::class, 'generateSku']);
    });
});
