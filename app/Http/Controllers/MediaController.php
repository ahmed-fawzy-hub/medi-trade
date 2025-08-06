<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Throwable;

class MediaController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/media",
 *     summary="List all active media and blog banner",
 *     tags={"Media"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string"),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="media", type="array", @OA\Items(type="object")),
 *                 @OA\Property(property="banner", type="object")
 *             )
 *         )
 *     )
 * )
 */
    public function index()
{
    try {
        $media = Media::where('is_active', true)->latest()->get()->map(function ($item) {
            $folder = $item->type === 'image' ? 'uploads/media/images' : 'uploads/media/videos';
            return [
                'id' => $item->id,
                'type' => $item->type,
                'file_path' => $item->file_path,
                'file_url' => asset($folder . '/' . $item->file_path),
                'video_url' => $item->video_url,
                'alt_text' => $item->alt_text,
                'is_active' => $item->is_active,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        $banner = Banner::where('page', 'blog')->latest()->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Active media and blog banner loaded successfully',
            'data' => [
                'media' => $media,
                'banner' => $banner,
            ]
        ]);
    } catch (Throwable $e) {
        return $this->errorResponse($e, 'Failed to load media or banner');
    }
}

    public function dashboardMedia()
    {
        try {
            $media = Media::latest()->get()->map(function ($item) {
    $folder = $item->type === 'image' ? 'uploads/media/images' : 'uploads/media/videos';
    return [
        'id' => $item->id,
        'type' => $item->type,
        'file_path' => $item->file_path,
        'file_url' => asset($folder . '/' . $item->file_path),
        'video_url' => $item->video_url,
        'alt_text' => $item->alt_text,
        'is_active' => $item->is_active,
        'created_at' => $item->created_at,
        'updated_at' => $item->updated_at,
    ];
});

        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to load dashboard media');
        }
    }
    /**
 * @OA\Post(
 *     path="/api/media",
 *     summary="Upload a new media file",
 *     tags={"Media"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"type", "file_path"},
 *                 @OA\Property(property="type", type="string", enum={"image", "video"}),
 *                 @OA\Property(property="file_path", type="string", format="binary"),
 *                 @OA\Property(property="video_url", type="string", format="url"),
 *                 @OA\Property(property="alt_text", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Media created successfully"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:image,video',
                'file_path' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi|max:10240',
                'video_url' => 'nullable|url',
                'alt_text' => 'nullable|string|max:255',
            ]);

            if ($validated['type'] === 'video' && empty($validated['video_url'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'video_url is required for videos'
                ], 422);
            }

            $file = $request->file('file_path');
            $folder = $validated['type'] === 'image' ? 'uploads/media/images' : 'uploads/media/videos';

            if (!File::exists(public_path($folder))) {
                File::makeDirectory(public_path($folder), 0755, true);
            }

            if ($validated['type'] === 'image') {
                $fileName = Str::uuid() . '.webp';
                Image::read($file)->toWebp(80)->save(public_path($folder . '/' . $fileName));
            } else {
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path($folder), $fileName);
            }

            $media = Media::create([
                'type' => $validated['type'],
                'file_path' => $fileName,
                'video_url' => $validated['video_url'] ?? null,
                'alt_text' => $validated['alt_text'] ?? null,
                'is_active' => 1,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Media created successfully',
                'data' => $media
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to create media');
        }
    }

    /**
 * @OA\Get(
 *     path="/api/media/{id}",
 *     summary="Show single media file",
 *     tags={"Media"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Media retrieved successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Media not found"
 *     )
 * )
 */


    public function show($id)
    {
        try {
            $media = Media::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Media found',
                'data' => $media
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to find media');
        }
    }
    /**
 * @OA\Put(
 *     path="/api/media/{id}",
 *     summary="Update media file",
 *     tags={"Media"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="type", type="string", enum={"image", "video"}),
 *                 @OA\Property(property="file_path", type="string", format="binary"),
 *                 @OA\Property(property="video_url", type="string", format="url"),
 *                 @OA\Property(property="alt_text", type="string")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Media updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Media not found"
 *     )
 * )
 */

    public function update(Request $request, $id)
    {
        try {
            $media = Media::findOrFail($id);

            $validated = $request->validate([
                'video_url' => 'nullable|url',
                'alt_text' => 'nullable|string|max:255',
                'is_active' => 'nullable|in:0,1',
                'file_path' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi|max:10240',
            ]);

            if ($media->type === 'video' && empty($validated['video_url']) && !$request->hasFile('file_path')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'video_url or file_path is required for videos'
                ], 422);
            }

            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                $folder = $media->type === 'image' ? 'uploads/media/images' : 'uploads/media/videos';

                $oldPath = public_path($folder . '/' . $media->file_path);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }

                if ($media->type === 'image') {
                    $fileName = Str::uuid() . '.webp';
                    Image::read($file)->toWebp(80)->save(public_path($folder . '/' . $fileName));
                } else {
                    $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path($folder), $fileName);
                }

                $validated['file_path'] = $fileName;
            }

            $media->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Media updated successfully',
                'data' => $media
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to update media');
        }
    }
/**
 * @OA\Patch(
 *     path="/api/media/{id}/toggle-visibility",
 *     summary="Toggle media visibility",
 *     tags={"Media"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Visibility toggled successfully"
 *     )
 * )
 */
    public function toggleVisibility($id)
    {
        try {
            $media = Media::findOrFail($id);
            $media->update(['is_active' => $media->is_active ? 0 : 1]);

            return response()->json([
                'status' => 'success',
                'message' => 'Media visibility toggled',
                'data' => ['is_active' => (int)$media->is_active]
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, 'Failed to toggle media visibility');
        }
    }

    /**
 * @OA\Get(
 *     path="/api/media/images",
 *     summary="Get only images",
 *     tags={"Media"},
 *     @OA\Response(
 *         response=200,
 *         description="List of image media"
 *     )
 * )
 */

    public function imagesOnly()
    {
        return $this->fetchByType('image', 'All active images fetched successfully');
    }

    /**
 * @OA\Get(
 *     path="/api/media/videos",
 *     summary="Get only videos",
 *     tags={"Media"},
 *     @OA\Response(
 *         response=200,
 *         description="List of video media"
 *     )
 * )
 */

    public function videosOnly()
    {
        return $this->fetchByType('video', 'All active videos fetched successfully');
    }

    /**
 * @OA\Get(
 *     path="/api/media/public/images",
 *     summary="Get all public images",
 *     description="Retrieve all public media files of type image.",
 *     tags={"Media"},
 *     @OA\Response(
 *         response=200,
 *         description="List of public images",
 *         @OA\JsonContent(type="array", @OA\Items(type="object"))
 *     )
 * )
 */

    public function publicImages()
    {
        return $this->fetchByType('image', 'Public images loaded');
    }

    /**
 * @OA\Get(
 *     path="/api/media/public/videos",
 *     summary="Get all public videos",
 *     description="Retrieve all public media files of type video.",
 *     tags={"Media"},
 *     @OA\Response(
 *         response=200,
 *         description="List of public videos",
 *         @OA\JsonContent(type="array", @OA\Items(type="object"))
 *     )
 * )
 */

    public function publicVideos()
    {
        return $this->fetchByType('video', 'Public videos loaded');
    }

    // =========================
    // Shared Helper Methods
    // =========================

    protected function fetchByType($type, $message)
    {
        try {
            $items = Media::where('type', $type)
                ->where('is_active', true)
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $items
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, "Failed to fetch $type");
        }
    }

    protected function errorResponse(Throwable $e, $msg)
    {
        return response()->json([
            'status' => 'error',
            'message' => $msg,
            'error' => $e->getMessage()
        ], 500);
    }
}
