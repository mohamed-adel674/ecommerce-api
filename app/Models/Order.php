<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    //protected $guarded = [];

    public function user(): BelongsTo
    {
        // الطلب ينتمي لمستخدم واحد
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        // الطلب لديه العديد من عناصر الطلب (OrderItem)
        return $this->hasMany(OrderItem::class);
    }
}
