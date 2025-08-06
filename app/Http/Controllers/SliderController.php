<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\ValidationException;
use Throwable;

class SliderController extends Controller
{
    public function index()
    {
        try {
            $sliders = Slider::latest()->get();
            return response()->json([
                'success' => true,
                'message' => 'All sliders fetched successfully',
                'data' => $sliders,
            ]);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title_en' => 'required|string|max:255',
                'title_ar' => 'required|string|max:255',
                'description_en' => 'required|string',
                'description_ar' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'video' => 'required|mimes:mp4,mov,avi,webm|max:10240',
                'is_active' => 'boolean'
            ]);

            if (!$request->hasFile('image') && !$request->hasFile('video')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must upload either an image or a video',
                ], 422);
            }

            $path = public_path('uploads/sliders');
            if (!File::exists($path)) File::makeDirectory($path, 0755, true);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = Str::uuid() . '.webp';
                Image::read($image)->toWebp(80)->save($path . '/' . $fileName);
                $validated['image'] = $fileName;
            }

            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $fileName = Str::uuid() . '.' . $video->getClientOriginalExtension();
                $video->move($path, $fileName);
                $validated['video'] = $fileName;
            }

            $slider = Slider::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Slider created successfully',
                'data' => $slider,
            ]);

        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $slider = Slider::findOrFail($id);

            $validated = $request->validate([
                'title_en' => 'required|nullable|string|max:255',
                'title_ar' => 'required|nullable|string|max:255',
                'description_en' => 'required|nullable|string',
                'description_ar' => 'required|nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'video' => 'nullable|mimes:mp4,mov,avi,webm|max:10240',
                'en_image_alt' => 'nullable|string|max:255',
                'ar_image_alt' => 'nullable|string|max:255',
                'en_video_alt' => 'nullable|string|max:255',
                'ar_video_alt' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            $path = public_path('uploads/sliders');
            if (!File::exists($path)) File::makeDirectory($path, 0755, true);

            if (
                !$request->hasFile('image') &&
                !$request->hasFile('video') &&
                !$slider->image &&
                !$slider->video
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must upload either an image or a video',
                ], 422);
            }

            if ($request->hasFile('image')) {
                if ($slider->image && File::exists($path . '/' . $slider->image)) {
                    File::delete($path . '/' . $slider->image);
                }

                $image = $request->file('image');
                $fileName = Str::uuid() . '.webp';
                Image::read($image)->toWebp(80)->save($path . '/' . $fileName);
                $validated['image'] = $fileName;

                if ($slider->video && File::exists($path . '/' . $slider->video)) {
                    File::delete($path . '/' . $slider->video);
                    $validated['video'] = null;
                }
            }

            if ($request->hasFile('video')) {
                if ($slider->video && File::exists($path . '/' . $slider->video)) {
                    File::delete($path . '/' . $slider->video);
                }

                $video = $request->file('video');
                $fileName = Str::uuid() . '.' . $video->getClientOriginalExtension();
                $video->move($path, $fileName);
                $validated['video'] = $fileName;

                if ($slider->image && File::exists($path . '/' . $slider->image)) {
                    File::delete($path . '/' . $slider->image);
                    $validated['image'] = null;
                }
            }

            $slider->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Slider updated successfully',
                'data' => $slider,
            ]);

        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $slider = Slider::findOrFail($id);
            $slider->is_active = !$slider->is_active;
            $slider->save();

            return response()->json([
                'success' => true,
                'message' => 'Slider status updated',
                'is_active' => (int) $slider->is_active,
            ]);
        } catch (Throwable $e) {
            return $this->handleException($e);
        }
    }

    private function handleException(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
        ], 500);
    }
}
