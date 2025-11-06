<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    //
    public function index()
    {
        // جلب الطلبات الأحدث أولاً، مع جلب علاقات المستخدم والمنتجات (N+1 fix)
        $orders = Order::with(['user', 'items.product']) 
                       ->latest()
                       ->paginate(20);
                       
        return response()->json($orders);
        return OrderResource::collection($orders);
    }



    public function show(Order $order) // Laravel يجلب كائن Order تلقائياً بناءً على الـ ID في الرابط
    {
        // عرض الطلب بتفاصيله الكاملة (إعادة تحميل العلاقات لضمان وجودها)
        return response()->json($order->load(['user', 'items.product']));
    }




    public function updateStatus(Request $request, Order $order)
    {
        // 1. التحقق من صحة الحالة الجديدة
        $request->validate([
            // نضمن أن القيمة المرسلة هي واحدة من هذه الحالات المسموح بها فقط
            'status' => 'required|in:pending,shipped,delivered,cancelled', 
        ]);

        // 2. تحديث الحالة
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => $order
        ]);
    }
}

