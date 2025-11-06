<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * GET /api/orders
     * عرض جميع طلبات المستخدم المصادق عليه (العميل).
     */
    public function index()
    {
        $user_id = Auth::id();

        // جلب الطلبات الخاصة بالمستخدم الحالي فقط
        $orders = Order::where('user_id', $user_id)
                       ->with('items.product') // جلب تفاصيل المنتجات داخل الطلب
                       ->latest()
                       ->paginate(10);
                       
        return response()->json([
            'message' => 'User order history retrieved successfully.',
            'orders' => $orders
        ]);
    }

    /**
     * GET /api/orders/{order}
     * عرض تفاصيل طلب محدد والتأكد من أنه يخص المستخدم الحالي.
     */
    public function show(Order $order)
    {
        // التحقق الأمني: تأكد أن الطلب يخص المستخدم الحالي
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized. This order does not belong to you.'
            ], 403); // 403 Forbidden
        }
        
        // جلب تفاصيل الطلب مع علاقاته
        return response()->json([
            'message' => 'Order details retrieved successfully.',
            'order' => $order->load('items.product')
        ]);
    }
}