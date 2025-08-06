<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Service;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Schema(
 *     schema="Service",
 *     type="object",
 *     title="Service",
 *     required={"id", "title_en", "title_ar"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title_en", type="string", example="Service Title EN"),
 *     @OA\Property(property="title_ar", type="string", example="عنوان الخدمة"),
 *     @OA\Property(property="short_description_en", type="string"),
 *     @OA\Property(property="short_description_ar", type="string"),
 *     @OA\Property(property="full_description_en", type="string"),
 *     @OA\Property(property="full_description_ar", type="string"),
 *     @OA\Property(property="meta_title", type="string"),
 *     @OA\Property(property="meta_description", type="string"),
 *     @OA\Property(property="slug_en", type="string"),
 *     @OA\Property(property="slug_ar", type="string"),
 *     @OA\Property(property="main_image", type="string"),
 *     @OA\Property(property="header_image", type="string"),
 *     @OA\Property(property="supplies_image", type="string"),
 *     @OA\Property(property="is_active", type="integer", example=1, enum={0,1}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class ServiceController extends Controller
{
    private function handleException(\Throwable $e, $customMessage = null)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'status' => false,
            'message' => $customMessage ?? 'Something went wrong',
            'error' => $e->getMessage(),
        ], 500);
    }

    private function saveImage($image, $folder)
    {
        $fileName = Str::uuid() . '.webp';
        $destinationPath = public_path("uploads/services/{$folder}");

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        Image::read($image)->toWebp(80)->save($destinationPath . '/' . $fileName);

        return $fileName;
    }

    private function deleteImage($imageName, $folder)
    {
        $path = public_path("uploads/services/{$folder}/{$imageName}");
        if ($imageName && File::exists($path)) {
            File::delete($path);
        }
    }
    /**
 * @OA\Get(
 *     path="/api/public/services",
 *     operationId="getPublicServices",
 *     tags={"Public Services"},
 *     summary="عرض خدمات الموقع للعامة",
 *     description="يعرض فقط الخدمات المفعّلة (is_active = true)",
 *     @OA\Response(
 *         response=200,
 *         description="قائمة الخدمات العامة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Service")
 *             )
 *         )
 *     )
 * )
 */

 public function publicWebsiteServices()
{
    try {
        $services = Service::where('is_active', true)->get();

        // استرجاع البنر الخاص بصفحة الخدمات
        $banner = Banner::where('page', 'services')->first();

        // عمل رابط كامل للصورة (لو بتحفظ فقط اسم الصورة)
        if ($banner && $banner->image) {
            $banner->image_url = asset('uploads/banners/' . $banner->image);
        }

        return response()->json([
            'status' => true,
            'services' => $services,
            'banner' => $banner,
        ]);
    } catch (\Throwable $e) {
        return $this->handleException($e);
    }
}
/**
 * @OA\Get(
 *     path="/api/services",
 *     operationId="getServices",
 *     tags={"Services"},
 *     summary="عرض كل الخدمات",
 *     description="يعرض قائمة بجميع الخدمات",
 *     @OA\Response(
 *         response=200,
 *         description="قائمة الخدمات",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Service")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="خطأ داخلي"
 *     )
 * )
 */

    public function index()
    {
        try {
            $services = Service::latest()->get()->map(function ($service) {
                return [
                    ...$service->toArray(),
                    'main_image_url' => asset("uploads/services/main_image/{$service->main_image}"),
                    'header_image_url' => asset("uploads/services/header_image/{$service->header_image}"),
                    'supplies_image_url' => asset("uploads/services/supplies_image/{$service->supplies_image}"),
                ];
            });

            return response()->json(['status' => true, 'data' => $services]);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
    /**
 * @OA\Get(
 *     path="/api/services/{id}",
 *     operationId="getServiceById",
 *     tags={"Services"},
 *     summary="عرض خدمة حسب ID",
 *     description="يعرض خدمة واحدة باستخدام رقم تعريفها",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="رقم تعريف الخدمة",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="بيانات الخدمة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/Service")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="لم يتم العثور على الخدمة"
 *     )
 * )
 */


    public function showById($id)
    {
        try {
            $service = Service::findOrFail($id);
            $serviceData = [
                ...$service->toArray(),
                'main_image_url' => asset("uploads/services/main_image/{$service->main_image}"),
                'header_image_url' => asset("uploads/services/header_image/{$service->header_image}"),
                'supplies_image_url' => asset("uploads/services/supplies_image/{$service->supplies_image}"),
            ];

            return response()->json(['status' => true, 'data' => $serviceData]);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Service not found');
        }
    }

    public function showActiveBySlug($slug)
    {
        try {
            $service = Service::where(function ($query) use ($slug) {
                $query->where('slug_en', $slug)->orWhere('slug_ar', $slug);
            })->where('is_active', true)->firstOrFail();

            return response()->json(['status' => true, 'data' => $service]);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Active service not found');
        }
    }

    /**
 * @OA\Post(
 *     path="/api/services",
 *     operationId="storeService",
 *     tags={"Services"},
 *     summary="إنشاء خدمة جديدة",
 *     description="ينشئ خدمة جديدة ببيانات كاملة وصور",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={
 *                     "title_en", "title_ar", "short_description_en", "short_description_ar",
 *                     "full_description_en", "full_description_ar",
 *                     "main_image", "header_image", "supplies_image",
 *                     "main_image_alt_en", "main_image_alt_ar",
 *                     "header_image_alt_en", "header_image_alt_ar",
 *                     "supplies_image_alt_en", "supplies_image_alt_ar",
 *                     "supplies_text_en", "supplies_text_ar"
 *                 },
 *                 @OA\Property(property="title_en", type="string"),
 *                 @OA\Property(property="title_ar", type="string"),
 *                 @OA\Property(property="short_description_en", type="string"),
 *                 @OA\Property(property="short_description_ar", type="string"),
 *                 @OA\Property(property="full_description_en", type="string"),
 *                 @OA\Property(property="full_description_ar", type="string"),
 *                 @OA\Property(property="meta_title", type="string"),
 *                 @OA\Property(property="meta_description", type="string"),
 *                 @OA\Property(property="main_image", type="file", format="binary"),
 *                 @OA\Property(property="header_image", type="file", format="binary"),
 *                 @OA\Property(property="supplies_image", type="file", format="binary"),
 *                 @OA\Property(property="main_image_alt_en", type="string"),
 *                 @OA\Property(property="main_image_alt_ar", type="string"),
 *                 @OA\Property(property="header_image_alt_en", type="string"),
 *                 @OA\Property(property="header_image_alt_ar", type="string"),
 *                 @OA\Property(property="supplies_image_alt_en", type="string"),
 *                 @OA\Property(property="supplies_image_alt_ar", type="string"),
 *                 @OA\Property(property="supplies_text_en", type="string"),
 *                 @OA\Property(property="supplies_text_ar", type="string"),
 *     @OA\Property(property="is_active", type="integer", example=1, enum={0,1}),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="تم إنشاء الخدمة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Service created successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/Service")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="أخطاء التحقق من الصحة"
 *     )
 * )
 */

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title_en' => 'required|string|max:255',
                'title_ar' => 'required|string|max:255',
                'short_description_en' => 'required|string',
                'short_description_ar' => 'required|string',
                'full_description_en' => 'required|string',
                'full_description_ar' => 'required|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'main_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'header_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'main_image_alt_en' => 'required|string|max:255',
                'main_image_alt_ar' => 'required|string|max:255',
                'header_image_alt_en' => 'required|string|max:255',
                'header_image_alt_ar' => 'required|string|max:255',
                'supplies_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'supplies_image_alt_en' => 'required|string|max:255',
                'supplies_image_alt_ar' => 'required|string|max:255',
                'supplies_text_en' => 'required|string',
                'supplies_text_ar' => 'required|string',
'is_active' => 'nullable|in:0,1',
            ]);

            $validated['slug_en'] = Str::slug($validated['title_en']);
            $validated['slug_ar'] = Str::slug($validated['title_ar']);

            $validated['main_image'] = $this->saveImage($request->file('main_image'), 'main_image');
            $validated['header_image'] = $this->saveImage($request->file('header_image'), 'header_image');
            $validated['supplies_image'] = $this->saveImage($request->file('supplies_image'), 'supplies_image');

            $service = Service::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Service created successfully',
                'data' => $service
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Error creating service');
        }
    }
    /**
 * @OA\Put(
 *     path="/api/services/{id}",
 *     operationId="updateService",
 *     tags={"Services"},
 *     summary="تحديث خدمة",
 *     description="تحديث بيانات خدمة موجودة باستخدام ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="رقم تعريف الخدمة",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="title_en", type="string"),
 *                 @OA\Property(property="title_ar", type="string"),
 *                 @OA\Property(property="short_description_en", type="string"),
 *                 @OA\Property(property="short_description_ar", type="string"),
 *                 @OA\Property(property="full_description_en", type="string"),
 *                 @OA\Property(property="full_description_ar", type="string"),
 *                 @OA\Property(property="meta_title", type="string"),
 *                 @OA\Property(property="meta_description", type="string"),
 *                 @OA\Property(property="main_image", type="file", format="binary"),
 *                 @OA\Property(property="header_image", type="file", format="binary"),
 *                 @OA\Property(property="supplies_image", type="file", format="binary"),
 *                 @OA\Property(property="main_image_alt_en", type="string"),
 *                 @OA\Property(property="main_image_alt_ar", type="string"),
 *                 @OA\Property(property="header_image_alt_en", type="string"),
 *                 @OA\Property(property="header_image_alt_ar", type="string"),
 *                 @OA\Property(property="supplies_image_alt_en", type="string"),
 *                 @OA\Property(property="supplies_image_alt_ar", type="string"),
 *                 @OA\Property(property="supplies_text_en", type="string"),
 *                 @OA\Property(property="supplies_text_ar", type="string"),
 *     @OA\Property(property="is_active", type="integer", example=1, enum={0,1}),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="تم تحديث الخدمة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Service updated successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/Service")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="لم يتم العثور على الخدمة"
 *     )
 * )
 */


    public function update(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);

            $validated = $request->validate([
                'title_en' => 'required|string|max:255',
                'title_ar' => 'required|string|max:255',
                'short_description_en' => 'required|string',
                'short_description_ar' => 'required|string',
                'full_description_en' => 'required|string',
                'full_description_ar' => 'required|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'main_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'header_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'main_image_alt_en' => 'required|string|max:255',
                'main_image_alt_ar' => 'required|string|max:255',
                'header_image_alt_en' => 'required|string|max:255',
                'header_image_alt_ar' => 'required|string|max:255',
                'supplies_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'supplies_image_alt_en' => 'required|string|max:255',
                'supplies_image_alt_ar' => 'required|string|max:255',
                'supplies_text_en' => 'required|string',
                'supplies_text_ar' => 'required|string',
'is_active' => 'nullable|in:0,1',
            ]);

            $validated['slug_en'] = Str::slug($validated['title_en']);
            $validated['slug_ar'] = Str::slug($validated['title_ar']);

            // Replace images if uploaded
            foreach (['main_image', 'header_image', 'supplies_image'] as $field) {
                if ($request->hasFile($field)) {
                    $this->deleteImage($service->$field, $field);
                    $validated[$field] = $this->saveImage($request->file($field), $field);
                }
            }

            $service->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Service updated successfully',
                'data' => $service
            ]);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Error updating service');
        }
    }
    /**
 * @OA\Post(
 *     path="/api/services/toggle-active/{id}",
 *     operationId="toggleServiceActive",
 *     tags={"Services"},
 *     summary="تفعيل/إلغاء تفعيل خدمة",
 *     description="يبدل حالة الخدمة بين مفعّلة وغير مفعّلة",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="رقم تعريف الخدمة",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="تم تغيير حالة الخدمة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Service activation toggled"),
 *             @OA\Property(property="data", ref="#/components/schemas/Service")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="لم يتم العثور على الخدمة"
 *     )
 * )
 */

    public function toggleStatus($id)
{
    try {
        $service = Service::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();

        return response()->json([
            'message' => 'Status updated',
            'is_active' => $service->is_active
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Service not found'
        ], 404);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Failed to update status',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
