<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function processCheckout(Request $request)
    {
        $user = Auth::user();
        
        // 1. التحقق من وجود سلة مشتريات للمستخدم وبها عناصر
        $cart = $user->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => '❌ لا يمكن متابعة الطلب. سلة المشتريات فارغة.'
            ], 400); // 400 Bad Request
        }
        
        // 2. استخدام المعاملات (Database Transaction)
        // هذا يضمن أنه إذا فشلت أي خطوة (مثل تحديث المخزون)، يتم التراجع عن كل التغييرات.
        try {
            DB::beginTransaction();

            // 3. إنشاء سجل الطلب الرئيسي
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending', // حالة أولية
                // TODO: (مهمة لاحقة) أضف هنا حقول العنوان والدفع
                'shipping_address' => $request->shipping_address ?? 'Not specified',
                'payment_method' => 'Stripe (Pending)',
                'total_amount' => 0, // سيتم تحديثه لاحقاً
            ]);

            $total = 0;
            $orderItemsData = [];

            // 4. معالجة عناصر السلة ونقلها للطلب
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

                // التحقق من المخزون
                if ($product->stock < $cartItem->quantity) {
                    DB::rollBack(); // التراجع عن إنشاء الطلب
                    return response()->json([
                        'message' => '❌ المخزون غير كافٍ للمنتج: ' . $product->name
                    ], 400);
                }

                // خصم الكمية من المخزون
                $product->decrement('stock', $cartItem->quantity);

                // إعداد بيانات عنصر الطلب
                $subtotal = $product->price * $cartItem->quantity;
                $total += $subtotal;
                
                $orderItemsData[] = new OrderItem([
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $product->price,
                ]);
            }
            
            // إضافة عناصر الطلب مرة واحدة
            $order->items()->saveMany($orderItemsData);

            // 5. تحديث الإجمالي الكلي للطلب وحذف محتويات السلة
            $order->total_amount = $total;
            $order->save();
            
            // حذف عناصر السلة بعد نقلها للطلب
            $cart->items()->delete();
            
            // 6. تأكيد المعاملة
            DB::commit();

            // 7. (مهمة لاحقة): هنا يجب دمج منطق الدفع (Stripe/Cashier)

            return response()->json([
                'message' => '✅ تم إنشاء الطلب بنجاح. في انتظار الدفع.',
                'order_id' => $order->id,
                'total' => $total,
                // يمكنك هنا إرجاع رابط الدفع إذا كنت تستخدم Stripe
                // 'payment_url' => '...' 
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            // تسجيل الخطأ للإدارة
            \Log::error('Checkout failed: ' . $e->getMessage()); 

            return response()->json([
                'message' => '❌ فشل في معالجة الطلب بسبب خطأ داخلي. يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }
}