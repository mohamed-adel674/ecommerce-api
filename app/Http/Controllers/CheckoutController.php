<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Route; // مطلوب لإنشاء روابط route()

class CheckoutController extends Controller
{
    /**
     * POST /checkout
     * معالجة الطلب (التحقق من المخزون، إنشاء الطلب) وإنشاء جلسة دفع Stripe.
     */
    public function processCheckout(Request $request)
    {
        $user = Auth::user();
        
        // 1. التحقق من السلة
        $cart = $user->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cannot proceed. The shopping cart is empty.'
            ], 400); // Bad Request
        }
        
        // 2. استخدام المعاملات (Database Transaction)
        try {
            DB::beginTransaction(); // بداية المعاملة

            // 3. إنشاء سجل الطلب الرئيسي (المنطق القديم من #013)
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending', 
                'shipping_address' => $request->shipping_address ?? 'Default Address',
                'payment_method' => 'Pending Payment',
                'total_amount' => 0, 
            ]);

            $total = 0;
            $orderItemsData = [];

            // 4. معالجة عناصر السلة ونقلها للطلب مع التحقق من المخزون
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;

                // التحقق من المخزون
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
            
            // تحديث الطلب وحذف السلة
            $order->items()->saveMany($orderItemsData);
            $order->total_amount = $total;
            $order->save();
            $cart->items()->delete();
            
            // 6. تأكيد المعاملة
            DB::commit(); 
            
            // 7. المنطق الجديد للمرحلة #014: تكامل الدفع
            
            $amountInCents = round($total * 100); 

            $checkoutSession = $user->checkout([
                [
                    'price_data' => [
                        'currency' => 'usd', // تأكد من العملة
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
            \Log::error('Checkout failed: ' . $e->getMessage()); 

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
        // يجب أن يتم تحديث حالة الطلب عبر Webhook وليس هنا
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