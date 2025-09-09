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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/refresh-token', function (Request $request) {
    try {
        // Обновляем токен
        $newToken = auth()->refresh();

        return response()->json([
            'access_token' => $newToken,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['error' => 'Invalid token'], 401);
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['error' => 'Token refresh failed'], 401);
    }
});

Route::resource('users', UserController::class);
Route::resource('products', ProductController::class);
Route::resource('orders', OrderController::class);
Route::middleware(['auth:api', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');

    Route::resource('orders', OrderAdminController::class);
    Route::resource('products', ProductAdminController::class);
});

