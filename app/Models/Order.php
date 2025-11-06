<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * الحقول التي يمكن تعبئتها بالجملة.
     * يجب إضافة 'stripe_session_id' للسماح بحفظه.
     */
    protected $fillable = [
        'user_id', 
        'status', 
        'shipping_address', 
        'payment_method', 
        'total_amount',
        'stripe_session_id', // <--- هذا هو الحقل الضروري للدفع عبر Stripe
    ];

    /**
     * تحديد العلاقة مع المستخدم الذي قام بإنشاء الطلب.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * تحديد العلاقة مع عناصر الطلب.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}