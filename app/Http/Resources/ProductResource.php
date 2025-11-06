<?php


namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => number_format($this->price, 2), // تنسيق السعر
            'stock' => $this->stock,
            'category' => new CategoryResource($this->whenLoaded('category')), // يجب إنشاء CategoryResource لاحقاً
            'image_url' => $this->image_url, // نفترض وجود حقل URL للصورة
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            // يتم إخفاء الحقول الحساسة مثل 'cost_price' أو 'internal_notes'
        ];
    }

    public function index(Request $request)
{
    // ... (منطق بناء الاستعلام) ...

    $products = $query->latest()->paginate(15);
    
    // استخدام ProductResource لتنسيق البيانات الناتجة
    // نستخدم collection لتنسيق بيانات Pagination
    return ProductResource::collection($products); 
}

public function show(Product $product)
{
    // استخدام Resource لتنسيق عرض المنتج المفرد
    return new ProductResource($product->load('category'));
}
}
