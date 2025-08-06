<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\ValidationException;

class BannerController extends Controller
{
    /**
     * عرض بيانات Banner حسب الصفحة
     */
    public function show($page)
    {
        try {
            $banner = Banner::where('page', $page)->first();

            if (!$banner) {
                return response()->json([
                    'status' => false,
                    'message' => 'Banner not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'image_url' => $banner->image ? asset('uploads/banners/' . $banner->image) : null,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }
public function index()
{
    try {
        $banners = Banner::all()->map(function ($banner) {
            return [
                ...$banner->toArray(),
                'image_url' => $banner->image ? asset('uploads/banners/' . $banner->image) : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $banners,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error retrieving banners',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'page' => 'required|string|unique:banners,page',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = Str::uuid() . '.webp';
            $destinationPath = public_path('uploads/banners');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            Image::read($image)->toWebp(80)->save($destinationPath . '/' . $fileName);
            $validated['image'] = $fileName;
        }

        $banner = Banner::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Banner created successfully',
            'data' => [
                ...$banner->toArray(),
                'image_url' => $banner->image ? asset('uploads/banners/' . $banner->image) : null,
            ]
        ]);
    } catch (\Throwable $e) {
        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'status' => false,
            'message' => 'Error creating banner',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * تحديث بيانات Banner حسب الصفحة
     */
    public function update(Request $request, $page)
    {
        try {
            $banner = Banner::where('page', $page)->first();

            if (!$banner) {
                return response()->json([
                    'status' => false,
                    'message' => 'Banner not found'
                ], 404);
            }

            $validated = $request->validate([
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'page' => 'required|string|unique:banners,page',
            ]);

            if ($request->hasFile('image')) {
                if ($banner->image && File::exists(public_path('uploads/banners/' . $banner->image))) {
                    File::delete(public_path('uploads/banners/' . $banner->image));
                }

                $image = $request->file('image');
                $fileName = Str::uuid() . '.webp';
                $destinationPath = public_path('uploads/banners');

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                Image::read($image)->toWebp(80)->save($destinationPath . '/' . $fileName);
                $validated['image'] = $fileName;
            }

            $banner->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Banner updated successfully',
                'data' => [
                    ...$banner->toArray(),
                    'image_url' => $banner->image ? asset('uploads/banners/' . $banner->image) : null,
                ]
            ]);
        } catch (\Throwable $e) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            return response()->json([
                'status' => false,
                'message' => 'Error updating banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
