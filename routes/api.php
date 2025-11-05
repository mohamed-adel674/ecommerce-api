// routes/api.php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\ProductController; 
// ... الخ

// مسارات سلة المشتريات (محمية)
Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    // ...
});

// مسارات الأدمن (محمية)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::apiResource('products', ProductController::class);
    // ...
});
// مسارات سلة المشتريات (محمية بضرورة تسجيل الدخول)
Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/add', [CartController::class, 'store']);
    Route::put('/update/{cartItem}', [CartController::class, 'update']);
    Route::delete('/remove/{cartItem}', [CartController::class, 'destroy']);
});