<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // إنشاء كاتيجوري جديدة
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name_en' => 'required|string|max:255',
                'name_ar' => 'required|string|max:255',
                'is_active' => 'nullable|in:0,1',
            ]);

            $validated['is_active'] = isset($validated['is_active']) ? (int) $validated['is_active'] : 0;

            $category = Category::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Category created successfully',
                'data' => $category,
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // تعديل كاتيجوري موجودة
    public function update(Request $request, Category $category)
    {
        try {
            $validated = $request->validate([
                'name_en' => 'required|string|max:255',
                'name_ar' => 'required|string|max:255',
                'is_active' => 'nullable|in:0,1',
            ]);

            $validated['is_active'] = isset($validated['is_active']) ? (int) $validated['is_active'] : 0;

            $category->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully',
                'data' => $category,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // جلب كل الكاتيجوري الفعالة
    public function index(Request $request)
{
    try {
        $lang = $request->get('lang', 'en');

        // احضار كل التصنيفات بدون فلتر التفعيل
        $categories = Category::latest()->get();

        $data = $categories->map(function ($c) use ($lang) {
            return [
                'id' => $c->id,
                'name' => $lang === 'ar' ? $c->name_ar : $c->name_en,
                'is_active' => (bool) $c->is_active, // يمكنك عرض حالة التفعيل أيضًا إن أردتِ
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'All categories fetched successfully',
            'data' => $data,
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch categories',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    // تفعيل أو تعطيل كاتيجوري
    public function toggleActive(Category $category)
    {
        try {
            $category->is_active = $category->is_active ? 0 : 1;
            $category->save();

            return response()->json([
                'status' => true,
                'message' => 'Category status toggled successfully',
                'data' => [
                    'id' => $category->id,
                    'new_status' => $category->is_active,
                ],
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to toggle category status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // عرض كاتيجوري واحدة
    public function show($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Category fetched successfully',
                'data' => $category,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch category',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
