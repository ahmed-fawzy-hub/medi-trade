<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * @OA\Schema(
 *     schema="Slider",
 *     type="object",
 *     title="Slider",
 *     required={"title_en", "title_ar", "description_en", "description_ar"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title_en", type="string", example="Welcome"),
 *     @OA\Property(property="title_ar", type="string", example="أهلاً"),
 *     @OA\Property(property="description_en", type="string"),
 *     @OA\Property(property="description_ar", type="string"),
 *     @OA\Property(property="image", type="string", example="slider1.webp"),
 *     @OA\Property(property="video", type="string", example="slider1.mp4"),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 */

class SliderController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/sliders",
 *     summary="Get all sliders",
 *     tags={"Sliders"},
 *     @OA\Response(
 *         response=200,
 *         description="List of sliders",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Slider"))
 *         )
 *     )
 * )
 */

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

    /**
 * @OA\Post(
 *     path="/api/sliders",
 *     summary="Create a new slider",
 *     tags={"Sliders"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"title_en", "title_ar", "description_en", "description_ar", "video"},
 *                 @OA\Property(property="title_en", type="string"),
 *                 @OA\Property(property="title_ar", type="string"),
 *                 @OA\Property(property="description_en", type="string"),
 *                 @OA\Property(property="description_ar", type="string"),
 *                 @OA\Property(property="image", type="file"),
 *                 @OA\Property(property="video", type="file"),
 *                 @OA\Property(property="is_active", type="boolean"),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Slider created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", ref="#/components/schemas/Slider")
 *         )
 *     )
 * )
 */

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

    /**
 * @OA\Put(
 *     path="/api/sliders/{id}",
 *     summary="Update an existing slider",
 *     tags={"Sliders"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Slider ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="title_en", type="string", example="Updated Title EN"),
 *                 @OA\Property(property="title_ar", type="string", example="Updated Title AR"),
 *                 @OA\Property(property="description_en", type="string", example="Updated Description EN"),
 *                 @OA\Property(property="description_ar", type="string", example="Updated Description AR"),
 *                 @OA\Property(property="image", type="file", description="Image file (jpeg, png, webp)"),
 *                 @OA\Property(property="video", type="file", description="Video file (mp4, mov, avi, webm)"),
 *                 @OA\Property(property="en_image_alt", type="string", example="English alt for image"),
 *                 @OA\Property(property="ar_image_alt", type="string", example="Arabic alt for image"),
 *                 @OA\Property(property="en_video_alt", type="string", example="English alt for video"),
 *                 @OA\Property(property="ar_video_alt", type="string", example="Arabic alt for video"),
 *                 @OA\Property(property="is_active", type="boolean", example=true),
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Slider updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Slider updated successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/Slider")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error or missing image/video",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="You must upload either an image or a video")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Slider not found"
 *     )
 * )
 */

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

    /**
 * @OA\Patch(
 *     path="/api/sliders/{id}/toggle-status",
 *     summary="Toggle the active status of a slider",
 *     tags={"Sliders"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Slider ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Slider status updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Slider status updated"),
 *             @OA\Property(property="is_active", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Slider not found"
 *     )
 * )
 */

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
