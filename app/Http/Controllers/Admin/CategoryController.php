<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str; // لاستخدام slugify
// يُفضل إنشاء CategoryRequest لفصل التحقق
// use App\Http\Requests\CategoryRequest; 

class CategoryController extends Controller
{
    /**
     * GET /api/admin/categories
     * عرض جميع الأقسام.
     */
    public function index()
    {
        return response()->json(Category::latest()->paginate(15));
    }

    /**
     * POST /api/admin/categories
     * إنشاء قسم جديد.
     */
    public function store(Request $request) // استبدل بـ CategoryRequest
    {
        // 1. التحقق من صحة البيانات
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            // يمكن إضافة صورة هنا لاحقاً
        ]);

        // 2. إنشاء القسم
        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name), // إنشاء slug آلياً
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category
        ], 201);
    }

    /**
     * GET /api/admin/categories/{category}
     * عرض قسم محدد.
     */
    public function show(Category $category)
    {
        return response()->json($category);
    }

    /**
     * PUT/PATCH /api/admin/categories/{category}
     * تحديث قسم محدد.
     */
    public function update(Request $request, Category $category) // استبدل بـ CategoryRequest
    {
        // 1. التحقق من صحة البيانات (تجاهل الاسم الحالي للـ unique check)
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        // 2. تحديث البيانات
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category
        ]);
    }

    /**
     * DELETE /api/admin/categories/{category}
     * حذف قسم محدد.
     */
    public function destroy(Category $category)
    {
        // يجب إضافة تحقق لعدم حذف قسم مرتبط بمنتجات!
        
        $category->delete();
        return response()->json(null, 204); // 204 No Content
    }
}