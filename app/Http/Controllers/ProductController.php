<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * عرض قائمة المنتجات العامة (مع التصفية والبحث).
     */
    public function index(Request $request)
    {
        // بناء الاستعلام: جلب المنتجات النشطة فقط
        $query = Product::where('is_active', true)->with('category');

        // مثال على إضافة منطق التصفية بالـ Category ID
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // مثال على منطق البحث بالاسم
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // تطبيق الترتيب وتحديد الصفحات
        $products = $query->latest()->paginate(15);
        
        return response()->json($products);
    }

    /**
     * عرض تفاصيل منتج واحد (بالـ Slug).
     * يتم جلب المنتج باستخدام الـ Slug بدلاً من الـ ID.
     */
    public function show(Product $product)
    {
        // نتحقق من أن المنتج نشط قبل عرضه
        if (!$product->is_active) {
            // نستخدم كود 404 بدلاً من 403 لأن المنتج غير موجود للعامة
            return response()->json(['message' => 'Product not found or inactive.'], 404); 
        }
        
        return response()->json($product->load('category'));
    }

    /**
     * عرض قائمة التصنيفات العامة.
     */
    public function categories()
    {
        // جلب التصنيفات النشطة فقط (نفترض أن جميع التصنيفات متاحة للعامة)
        $categories = Category::select('id', 'name', 'slug')->get(); 
        return response()->json($categories);
    }
}