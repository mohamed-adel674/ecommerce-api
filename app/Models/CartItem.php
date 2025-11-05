<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    //
    protected $guarded = [];

    public function cart(): BelongsTo
    {
        // عنصر السلة ينتمي لسلة واحدة
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        // عنصر السلة مرتبط بمنتج واحد
        return $this->belongsTo(Product::class);
    }
}
