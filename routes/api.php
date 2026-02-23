<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\AdminController;


// Публичные маршруты (не требуют авторизации)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Маршруты для авторизованных пользователей
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);

    // Order Items
    Route::get('/order-items', [OrderItemController::class, 'index']);
    Route::post('/order-items', [OrderItemController::class, 'store']);
    Route::get('/order-items/{order_item}', [OrderItemController::class, 'show']);
    Route::put('/order-items/{order_item}', [OrderItemController::class, 'update']);
    Route::delete('/order-items/{order_item}', [OrderItemController::class, 'destroy']);
});

// Маршруты для админов
Route::prefix('admin')->middleware(['auth:api', 'admin'])->group(function () {
    // Admin dashboard
    Route::get('/', [AdminController::class, 'index']);

    // Admin Orders
    Route::get('/orders', [OrderAdminController::class, 'index']);
    Route::post('/orders', [OrderAdminController::class, 'store']);
    Route::get('/orders/{order}', [OrderAdminController::class, 'show']);
    Route::put('/orders/{order}', [OrderAdminController::class, 'update']);
    Route::delete('/orders/{order}', [OrderAdminController::class, 'destroy']);
    Route::get('/orders/statistics', [OrderAdminController::class, 'statistics']);
    Route::put('/orders/{order}/status', [OrderAdminController::class, 'updateStatus']);
    Route::get('/orders/{order}/items', [OrderItemController::class, 'getByOrder']);

    // Admin Products
    Route::get('/products', [ProductAdminController::class, 'index']);
    Route::post('/products', [ProductAdminController::class, 'store']);
    Route::get('/products/{product}', [ProductAdminController::class, 'show']);
    Route::put('/products/{product}', [ProductAdminController::class, 'update']);
    Route::delete('/products/{product}', [ProductAdminController::class, 'destroy']);

    // Admin Order Items
    Route::get('/order-items', [OrderItemController::class, 'index']);
    Route::post('/order-items', [OrderItemController::class, 'store']);
    Route::get('/order-items/{order_item}', [OrderItemController::class, 'show']);
    Route::put('/order-items/{order_item}', [OrderItemController::class, 'update']);
    Route::delete('/order-items/{order_item}', [OrderItemController::class, 'destroy']);
});
