<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\ValidationException;

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
                'is_active' => 'boolean',
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
                'is_active' => 'boolean',
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
}
