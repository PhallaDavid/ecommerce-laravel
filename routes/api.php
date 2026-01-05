<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductSearchController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserRoleController;

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);
Route::get('/user', [AuthController::class, 'user']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
Route::post('/products/{id}/reviews', [ReviewController::class, 'store'])->middleware('auth:sanctum');
Route::apiResource('banners', BannerController::class);
Route::post('/orders/{orderId}/location', [DeliveryController::class, 'updateLocation']);
Route::get('/orders/{orderId}/location', [DeliveryController::class, 'getLocation']);
    Route::get('/new-arrivals', [ProductController::class, 'newArrivals']);


// Categories
Route::apiResource('categories', CategoryController::class);

// Products - Public routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/promotion', [ProductController::class, 'promotion']);
    Route::get('/search', [ProductSearchController::class, 'search']);
    Route::get('/category/{id}', [ProductController::class, 'productsByCategory']);
    Route::get('/category/slug/{slug}', [ProductController::class, 'productsByCategorySlug']);
    Route::get('/{id}', [ProductController::class, 'show']);
});

// Products - Protected routes (require authentication)
Route::middleware('auth:sanctum')->prefix('products')->group(function () {
    Route::post('/', [ProductController::class, 'store']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/profile',  [AuthController::class, 'profile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('products/{productId}/favorite', [ProductController::class, 'addFavorite']);
    Route::delete('products/{productId}/favorite', [ProductController::class, 'removeFavorite']);
    Route::get('products/favorites', [ProductController::class, 'favorites']);
    Route::post('products/{id}/cart', [ProductController::class, 'addToCart']);
    Route::delete('products/{id}/cart', [ProductController::class, 'removeFromCart']);
    Route::get('/cart', [ProductController::class, 'cart']);
});
Route::post('/payments/create', [PaymentController::class, 'create']);
Route::get('/payment/status/{md5}', [PaymentController::class, 'checkStatus']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::get('/orders/history', [OrderController::class, 'history']);
    Route::get('/orders/{id}', [OrderController::class, 'getOrderById']);
});

// Admin Routes
use App\Http\Controllers\AdminController;

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard Stats
    Route::get('/dashboard/stats', [AdminController::class, 'dashboardStats']);

    // Users Management
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::get('/users/{id}', [AdminController::class, 'getUser']);
    Route::post('/users', [AdminController::class, 'createUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

    // Products Management
    Route::get('/products', [AdminController::class, 'getProducts']);
    Route::post('/products', [AdminController::class, 'createProduct']);
    Route::match(['put', 'post'], '/products/{id}', [AdminController::class, 'updateProduct']);
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);

    // Orders Management
    Route::get('/orders', [AdminController::class, 'getOrders']);
    Route::get('/orders/{id}', [AdminController::class, 'getOrder']);
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
});

// RBAC Routes - User, Role, Permission Management
Route::middleware(['auth:sanctum'])->prefix('admin/rbac')->group(function () {
    // User Management
    Route::middleware(['permission:view-users'])->group(function () {
        Route::get('/users', [UserRoleController::class, 'index']);
        Route::get('/users/{id}', [UserRoleController::class, 'show']);
        Route::get('/users/{id}/permissions', [UserRoleController::class, 'getPermissions']);
    });

    Route::middleware(['permission:create-users'])->group(function () {
        Route::post('/users', [UserRoleController::class, 'store']);
    });

    Route::middleware(['permission:update-users'])->group(function () {
        Route::put('/users/{id}', [UserRoleController::class, 'update']);
    });

    Route::middleware(['permission:delete-users'])->group(function () {
        Route::delete('/users/{id}', [UserRoleController::class, 'destroy']);
    });

    Route::middleware(['permission:assign-roles'])->group(function () {
        Route::post('/users/{id}/roles', [UserRoleController::class, 'assignRole']);
    });

    Route::middleware(['permission:revoke-roles'])->group(function () {
        Route::delete('/users/{id}/roles/{roleId}', [UserRoleController::class, 'revokeRole']);
    });

    // Role Management
    Route::middleware(['permission:view-roles'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::get('/roles/{id}', [RoleController::class, 'show']);
        Route::get('/roles/{id}/permissions', [RoleController::class, 'getPermissions']);
    });

    Route::middleware(['permission:create-roles'])->group(function () {
        Route::post('/roles', [RoleController::class, 'store']);
    });

    Route::middleware(['permission:update-roles'])->group(function () {
        Route::put('/roles/{id}', [RoleController::class, 'update']);
    });

    Route::middleware(['permission:delete-roles'])->group(function () {
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    });

    Route::middleware(['permission:assign-permissions'])->group(function () {
        Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermission']);
    });

    Route::middleware(['permission:revoke-permissions'])->group(function () {
        Route::delete('/roles/{id}/permissions/{permissionId}', [RoleController::class, 'revokePermission']);
    });

    // Permission Management
    Route::middleware(['permission:view-permissions'])->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/{id}', [PermissionController::class, 'show']);
        Route::get('/permissions/{id}/roles', [PermissionController::class, 'getRoles']);
    });

    Route::middleware(['permission:create-permissions'])->group(function () {
        Route::post('/permissions', [PermissionController::class, 'store']);
    });

    Route::middleware(['permission:update-permissions'])->group(function () {
        Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    });

    Route::middleware(['permission:delete-permissions'])->group(function () {
        Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
    });
});
