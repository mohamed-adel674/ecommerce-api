<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route; 
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart; // تم إضافة استيراد نموذج السلة بشكل صحيح

class CheckoutController extends Controller
{
    /**
     * POST /checkout
     * معالجة الطلب (التحقق من المخزون، إنشاء الطلب) وإنشاء جلسة دفع Stripe.
     */
    public function processCheckout(Request $request)
    {
        // 1. التحقق من مصادقة المستخدم
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        
        // 2. التحقق من السلة (يجب أن تعمل دالة cart() الآن)
        $cart = $user->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cannot proceed. The shopping cart is empty.'
            ], 400); // Bad Request
        }
        
        // 3. استخدام المعاملات (Database Transaction) لضمان سلامة البيانات
        try {
            DB::beginTransaction(); // بداية المعاملة

            // 4. إنشاء سجل الطلب الرئيسي
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending', 
                'shipping_address' => $request->shipping_address ?? 'Default Address',
                'payment_method' => 'Pending Payment',
                'total_amount' => 0, 
            ]);

            $total = 0;
            $orderItemsData = [];

            // 5. معالجة عناصر السلة ونقلها للطلب مع التحقق من المخزون
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

                // التحقق من المخزون (يجب أن يكون المخزون قابلاً للتحديث/التناقص بشكل حصري داخل المعاملة)
                if ($product->stock < $cartItem->quantity) {
                    DB::rollBack(); 
                    return response()->json([
                        'message' => 'Insufficient stock for product: ' . $product->name
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
            
            // 6. تحديث الطلب وحذف السلة
            $order->items()->saveMany($orderItemsData);
            $order->total_amount = $total;
            $order->save();
            
            // حذف عناصر السلة بعد نقلها إلى الطلب
            $cart->items()->delete();
            
            // 7. تأكيد المعاملة بعد نجاح جميع عمليات قاعدة البيانات
            DB::commit(); 
            
            $amountInCents = round($total * 100); 

            $checkoutSession = $user->checkout([
                [
                    'price_data' => [
                        'currency' => 'usd', // تأكد من العملة المستخدمة في مشروعك
                        'unit_amount' => $amountInCents,
                        'product_data' => [
                            'name' => 'Order #' . $order->id . ' Payment',
                            'description' => 'E-commerce Order Payment',
                        ],
                    ],
                    'quantity' => 1,
                ],
            ], [
                // استخدام دوال route() لتحديد مسارات الويب
                'success_url' => route('checkout.success', ['order' => $order->id]), 
                'cancel_url' => route('checkout.cancel', ['order' => $order->id]), 
                'metadata' => [
                    'order_id' => $order->id,
                ],
            ]);
            
            // حفظ ID الجلسة في الطلب
            $order->stripe_session_id = $checkoutSession->id;
            $order->save();

            // إرجاع رابط الدفع إلى العميل
            return response()->json([
                'message' => 'Order created successfully. Redirecting to payment.',
                'order_id' => $order->id,
                'checkout_url' => $checkoutSession->url, 
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed for user ' . ($user ? $user->id : 'N/A') . ': ' . $e->getMessage()); 

            return response()->json([
                'message' => 'Failed to process checkout due to an internal error. Please try again later.'
            ], 500);
        }
    }
    
    /**
     * مسار التوجيه بعد نجاح الدفع (يتم استدعاؤه بواسطة متصفح العميل من Stripe)
     */
    public function success(Order $order)
    {
        // توجيه العميل لصفحة في الفرونت إند لمعالجة عرض رسالة النجاح
        // (تحديث حالة الطلب يجب أن يتم عبر Webhook)
        return redirect('https://your-frontend.com/order-success?order_id=' . $order->id); 
    }

    /**
     * مسار التوجيه بعد إلغاء الدفع (يتم استدعاؤه بواسطة متصفح العميل من Stripe)
     */
    public function cancel(Order $order)
    {
        // توجيه العميل لصفحة في الفرونت إند لعرض رسالة الإلغاء
        return redirect('https://your-frontend.com/cart?status=cancelled');
    }
}