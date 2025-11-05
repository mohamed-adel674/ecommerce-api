// routes/api.php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\ProductController; 
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;


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


// مسارات عامة لا تحتاج لتسجيل دخول
Route::get('products', [ProductController::class, 'index']);
// استخدام {product:slug} لتحديد أن الـ Route Model Binding يجب أن يتم عبر حقل slug
Route::get('products/{product:slug}', [ProductController::class, 'show']); 
Route::get('categories', [ProductController::class, 'categories']);


// مسارات المصادقة (عامة)
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// مسار تسجيل الخروج (محمي بـ Sanctum Token)
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);


// 1. المسارات العامة (لا تحتاج Token)
Route::post('/login', [AuthController::class, 'login']); 
Route::post('/register', [AuthController::class, 'register']); // سنضيف هذه لاحقاً

// 2. المسارات المحمية (تحتاج Token)
// نقوم بتجميع المسارات التي تحتاج مصادقة داخل هذا الـ Middleware
Route::middleware('auth:sanctum')->group(function () {
    
    // هذا المسار لن يعمل إلا إذا تم إرسال Token صالح
    Route::get('/user', [AuthController::class, 'userDetails']);

    // هنا ستضاف مسارات سلة المشتريات والطلبات لاحقاً
    // Route::post('/cart/add', ...); 
});

