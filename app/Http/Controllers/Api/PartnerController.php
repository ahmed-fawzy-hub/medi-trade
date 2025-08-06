<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class PartnerController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name_en' => 'required|string|max:255',
                'name_ar' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                'category_id' => 'required|exists:categories,id',
                'en_alt_image' => 'required|string|max:255',
                'ar_alt_image' => 'required|string|max:255',
            ]);

            $image = $request->file('image');
            $fileName = Str::uuid() . '.webp';
            $path = public_path('uploads/partners');

            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            Image::read($image)
                ->toWebp(80)
                ->save($path . '/' . $fileName);

            $validated['image'] = $fileName;

            $partner = Partner::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Partner created successfully',
                'data' => [
                    'id' => $partner->id,
                    'name_en' => $partner->name_en,
                    'name_ar' => $partner->name_ar,
                    'image_url' => asset('uploads/partners/' . $partner->image),
                    'category_id' => $partner->category_id,
                    'en_alt_image' => $partner->en_alt_image,
                    'ar_alt_image' => $partner->ar_alt_image,
                    'is_active' => $partner->is_active,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while creating the partner',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllByCategory($categoryId)
    {
        try {
            $partners = Partner::where('category_id', $categoryId)->latest()->get();

            $data = $partners->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name_en' => $p->name_en,
                    'name_ar' => $p->name_ar,
                    'image_url' => $p->image ? asset('uploads/partners/' . $p->image) : null,
                    'category_id' => $p->category_id,
                    'en_alt_image' => $p->en_alt_image,
                    'ar_alt_image' => $p->ar_alt_image,
                    'is_active' => $p->is_active,
                ];
            });

            return response()->json([
                'status' => true,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch partners',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getActiveByCategory($categoryId, Request $request)
    {
        try {
            $lang = $request->get('lang', 'en');

            $partners = Partner::where('category_id', $categoryId)
                ->where('is_active', true)
                ->latest()
                ->get();

            $data = $partners->map(function ($p) use ($lang) {
                return [
                    'name' => $lang === 'ar' ? $p->name_ar : $p->name_en,
                    'image_url' => $p->image ? asset('uploads/partners/' . $p->image) : null,
                    'category_id' => $p->category_id,
                    'alt' => $lang === 'ar' ? $p->ar_alt_image : $p->en_alt_image,
                ];
            });

            $banner = Banner::where('page', 'partner')->first();

            return response()->json([
                'status' => true,
                'data' => [
                    'banner' => [
                        'image_url' => $banner?->image ? asset('uploads/banners/' . $banner->image) : null,
                        'alt' => $lang === 'ar' ? $banner?->ar_alt_image : $banner?->en_alt_image,
                        'title' => $lang === 'ar' ? $banner?->title_ar : $banner?->title_en,
                    ],
                    'partners' => $data,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch active partners and banner',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleActive(Partner $partner)
    {
        try {
            $partner->is_active = !$partner->is_active;
            $partner->save();

            return response()->json([
                'status' => true,
                'message' => 'Partner status toggled successfully',
                'new_status' => $partner->is_active,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to toggle partner status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Partner $partner)
    {
        try {
            $validated = $request->validate([
                'name_en' => 'required|string|max:255',
                'name_ar' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'category_id' => 'required|exists:categories,id',
                'en_alt_image' => 'nullable|string|max:255',
                'ar_alt_image' => 'nullable|string|max:255',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = Str::uuid() . '.webp';
                $path = public_path('uploads/partners');

                if (!File::exists($path)) {
                    File::makeDirectory($path, 0755, true);
                }

                if ($partner->image && File::exists($path . '/' . $partner->image)) {
                    File::delete($path . '/' . $partner->image);
                }

                Image::read($image)
                    ->toWebp(80)
                    ->save($path . '/' . $fileName);

                $validated['image'] = $fileName;
            }

            $partner->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Partner updated successfully',
                'data' => [
                    'id' => $partner->id,
                    'name_en' => $partner->name_en,
                    'name_ar' => $partner->name_ar,
                    'image_url' => $partner->image ? asset('uploads/partners/' . $partner->image) : null,
                    'category_id' => $partner->category_id,
                    'en_alt_image' => $partner->en_alt_image,
                    'ar_alt_image' => $partner->ar_alt_image,
                    'is_active' => $partner->is_active,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update partner',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
