<?php

namespace App\Http\Controllers;

use App\Models\AboutUs;
use App\Models\Banner;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\ValidationException;

class AboutUsController extends Controller
{
    /**
     * دالة لعرض بيانات AboutUs فقط (Dashboard)
     */
    public function show()
    {
        try {
            $about = AboutUs::first();

            return response()->json([
                'status' => true,
                'data' => $about
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching about us data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * دالة لعرض بيانات AboutUs + Services (Public Website)
     */
    public function showWithServices()
{
    try {
        $about = AboutUs::first();
        $services = Service::where('is_active', true)->get();
        $banner = Banner::where('page', 'about')->first();

        return response()->json([
            'status' => true,
            'data' => [
                'about' => $about,
                'services' => $services,
                'banner' => [
                    'image_url' => $banner?->image ? asset('uploads/banners/' . $banner->image) : null,
                ]
            ]
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error fetching about us, services, and banner',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * تعديل بيانات AboutUs
     */
    public function update(Request $request)
{
    try {
        // احصل على أول سجل أو أنشئ واحد جديد إذا غير موجود
        $about = AboutUs::first();
        if (!$about) {
            $about = AboutUs::create(); // يمكنك تمرير بيانات ابتدائية هنا إذا أردت
        }

        $validated = $request->validate([
            'title_en' => 'required|string|max:255',
            'title_ar' => 'required|string|max:255',
            'home_description_en' => 'required|string',
            'home_description_ar' => 'required|string',
            'about_description_en' => 'required|string',
            'about_description_ar' => 'required|string',
            'mission_en' => 'nullable|string',
            'mission_ar' => 'nullable|string',
            'vision_en' => 'nullable|string',
            'vision_ar' => 'nullable|string',
            'investments_en' => 'nullable|string',
            'investments_ar' => 'nullable|string',
            'why_medi_trade_en' => 'nullable|string',
            'why_medi_trade_ar' => 'nullable|string',
            'en_alt_image' => 'required|string|max:255',
            'ar_alt_image' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // التعامل مع الصورة
        if ($request->hasFile('image')) {
            if ($about->image && File::exists(public_path('uploads/about-us/' . $about->image))) {
                File::delete(public_path('uploads/about-us/' . $about->image));
            }

            $image = $request->file('image');
            $fileName = Str::uuid() . '.webp';
            $destinationPath = public_path('uploads/about-us');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            Image::read($image)
                ->toWebp(80)
                ->save($destinationPath . '/' . $fileName);

            $validated['image'] = $fileName;
        }

        // تحديث السجل
        $about->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'About Us updated successfully',
            'data' => [
                ...$about->toArray(),
                'image_url' => $about->image ? asset('uploads/about-us/' . $about->image) : null,
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
            'message' => 'Error while updating About Us',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
