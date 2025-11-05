<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    //
    protected $guarded = []; // السماح بتعبئة كل الحقول بسهولة في هذا النموذج

    public function user(): BelongsTo
    {
        // السلة تنتمي لمستخدم واحد
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        // السلة لديها العديد من عناصر السلة (CartItem)
        return $this->hasMany(CartItem::class);
    }
}
