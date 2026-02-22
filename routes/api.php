<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::apiResource('products', ProductController::class);

    Route::apiResource('orders', OrderController::class)->only([
        'index',
        'show',
        'store',
    ]);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);

    Route::middleware('is_admin')->group(function () {
        Route::post('orders/{id}/accept', [OrderController::class, 'accept']);
        Route::post('orders/{id}/reject', [OrderController::class, 'reject']);
    });
});