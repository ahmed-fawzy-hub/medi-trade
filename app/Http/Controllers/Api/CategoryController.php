<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;


class CategoryController extends Controller
{
    // إنشاء كاتيجوري جديدة
        /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name_en", "name_ar"},
     *             @OA\Property(property="name_en", type="string"),
     *             @OA\Property(property="name_ar", type="string"),
     *             @OA\Property(property="is_active", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     )
     * )
     */

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
        /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Update an existing category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name_en", "name_ar"},
     *             @OA\Property(property="name_en", type="string"),
     *             @OA\Property(property="name_ar", type="string"),
     *             @OA\Property(property="is_active", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated"
     *     )
     * )
     */

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
        /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="lang",
     *         in="query",
     *         description="Language (en or ar)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="is_active", type="boolean")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

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
        /**
     * @OA\Patch(
     *     path="/api/categories/{id}/toggle",
     *     summary="Toggle category active status",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status toggled"
     *     )
     * )
     */

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
        /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Get category by ID",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */

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
