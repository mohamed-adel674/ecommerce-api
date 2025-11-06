<?php

// app/Http/Resources/OrderResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'shipping_address' => $this->shipping_address,
            'total_amount' => number_format($this->total_amount, 2),
            'stripe_session_id' => $this->stripe_session_id, // مفيد للمراجعة
            'ordered_on' => $this->created_at->format('Y-m-d H:i:s'),
            // تطبيق OrderItemResource على قائمة العناصر
            'items' => OrderItemResource::collection($this->whenLoaded('items')), 
        ];
    }
}
