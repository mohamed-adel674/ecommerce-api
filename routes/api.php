<?php

use Illuminate\Support\Facades\Route;
// استيراد جميع المتحكمات
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController; // المتحكم العام للمنتجات
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
// يجب التأكد أن هذه الفئات موجودة
use App\Http\Controllers\Admin\ProductController as AdminProductController; // متحكم الأدمن للمنتجات
use App\Http\Controllers\Admin\OrderController as AdminOrderController; // متحكم الأدمن للطلبات
use App\Http\Controllers\OrderController; // سنستخدم متحكم جديد لطلبات العميل
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| هنا يتم تعريف مسارات الـ API (عادةً ما ترجع بيانات JSON)
*/

// --- 1. المسارات العامة (Public Routes) ---
// لا تحتاج مصادقة (Auth Token)

// مسارات المصادقة (Auth)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// مسارات المنتجات العامة (للعرض فقط)
Route::get('products', [ProductController::class, 'index']);
// استخدام {product:slug} للبحث بالـ slug
Route::get('products/{product:slug}', [ProductController::class, 'show']);


// --- 2. مسارات الويب Hook (يجب أن تكون في routes/web.php لكن نضعها هنا مؤقتاً لسهولة المراجعة) ---
// يجب وضع مسارات Webhook (Stripe) و Success/Cancel في routes/web.php
// لأنها تتعامل مع إعادة توجيه المتصفح، لكن سنتركها هنا للمراجعة.

// مسارات الإعادة التوجيه لـ Stripe (يستخدمها المتصفح بعد الدفع)
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel/{order}', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// مسار الـ Webhook (يستخدمه خادم Stripe لتأكيد الدفع)
Route::post('stripe/webhook', \Laravel\Cashier\Http\Controllers\WebhookController::class);


// --- 3. المسارات المحمية (Authenticated Routes) ---
// تحتاج إلى Sanctum Token سارٍ (للكاتب والعميل)
Route::middleware('auth:sanctum')->group(function () {

    // مسار تسجيل الخروج ومعلومات المستخدم
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'userDetails']);

    // مسارات سلة المشتريات
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'store']);
        Route::put('/update/{cartItem}', [CartController::class, 'update']);
        Route::delete('/remove/{cartItem}', [CartController::class, 'destroy']);
    });

    // مسار معالجة الطلب (Checkout) - المرحلة #013/014
    Route::post('/checkout', [CheckoutController::class, 'processCheckout']);

    Route::get('products', [ProductController::class, 'index']);

    // --- 4. مسارات لوحة تحكم الأدمن (Admin Protected Routes) ---
    // يجب تطبيق الواسطة 'auth:sanctum' و 'role:admin' معاً هنا لضمان عمل الحماية
    Route::group([
        'prefix' => 'admin',
        'middleware' => ['auth:sanctum', 'role:admin'] // التصحيح: إضافة auth:sanctum مجدداً
    ], function () {

        // إدارة المنتجات (المهمة #015)
        Route::get('/products', [AdminProductController::class, 'index']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::put('/products/{product}', [AdminProductController::class, 'update']);
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy']);

        // إدارة الطلبات (المهمة #016) - الكود المكتمل
        // عرض جميع الطلبات
        Route::get('/orders', [AdminOrderController::class, 'index']);
        // عرض تفاصيل طلب واحد
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        // تحديث حالة الطلب
        Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

        // مسارات العميل لسجل الطلبات (المهمة #017)
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);

        // 3. إدارة الأقسام (المهمة #018)
        Route::apiResource('categories', AdminCategoryController::class);
    });
});
