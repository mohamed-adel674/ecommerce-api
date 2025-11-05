<?php
// app/Http/Controllers/CartController.php
namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // لاستخدام المستخدم الحالي

class CartController extends Controller
{
    // 1. منطق إضافة/تحديث المنتج للسلة
    public function store(AddToCartRequest $request)
    {
        // 1. جلب بيانات المستخدم والسلة
        // Auth::user() هو المستخدم المسجل دخوله حالياً
        $user = Auth::user(); 

        // جلب السلة للمستخدم، أو إنشاؤها إذا لم تكن موجودة بعد
        $cart = $user->cart()->firstOrCreate([]); 

        // 2. التحقق من وجود العنصر في السلة
        $cartItem = $cart->items()
                         ->where('product_id', $request->product_id)
                         ->first();

        if ($cartItem) {
            // إذا كان موجوداً: قم بزيادة الكمية المطلوبة
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
            $message = 'Product quantity updated in cart.';
        } else {
            // إذا لم يكن موجوداً: قم بإضافته كعنصر جديد
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
            $message = 'Product added to cart.';
        }

        // 3. الرد على العميل بالنجاح
        return response()->json([
            'message' => $message,
            'cart_item' => $cartItem->load('product'), // نعيد بيانات العنصر كاملاً مع المنتج
        ], 201);
    }

    // 2. منطق عرض السلة
    public function index()
    {
        // جلب السلة وعناصرها ومنتجاتها (لتجنب Query N+1)
        $cart = Auth::user()->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 200);
        }

        // TODO: (مهمة لاحقة) استخدام CartResource لتنسيق البيانات والحسابات الإجمالية
        return response()->json($cart);
    }

    // ... يمكنك إضافة دوال update و destroy بنفس المنهجية
}