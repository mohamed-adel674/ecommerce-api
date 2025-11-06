<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * عرض جميع المنتجات مع دعم البحث والتصفية عبر Query Parameters.
     */
    public function index(Request $request)
    {
        // نبدأ ببناء استعلام المنتجات الأساسي
        $query = Product::query()->with('category'); // Always load the category relation

        // 1. التصفية حسب البحث (Search by Keyword)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            // البحث عن الكلمة المفتاحية في اسم المنتج أو وصفه
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
        }

        // 2. التصفية حسب القسم (Filter by Category Slug)
        if ($request->has('category') && $request->category != '') {
            $categorySlug = $request->category;
            // يجب أن نستخدم whereHas للفلترة على علاقة القسم (Category)
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // 3. التصفية حسب نطاق السعر (Filter by Price Range)
        if ($request->has('min_price') && is_numeric($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        // 4. تنفيذ الاستعلام وإرجاع النتيجة (ترتيب حسب الأحدث)
        $products = $query->latest()->paginate(15);
        
        return response()->json($products);
    }

    /**
     * GET /api/products/{product:slug}
     * عرض تفاصيل منتج محدد.
     */
    public function show(Product $product)
    {
        // تأكد من أن هذه الدالة موجودة
        return response()->json($product->load('category'));
    }
}