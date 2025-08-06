<?php

namespace App\Http\Controllers;

use App\Models\AboutUs;
use App\Models\Category;
use App\Models\Media;
use App\Models\Partner;
use App\Models\Service;
use App\Models\Slider;
use Illuminate\Http\Request;
use Throwable;

class HomeController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/home",
 *     summary="Get homepage data",
 *     tags={"Home"},
 *     @OA\Response(
 *         response=200,
 *         description="Homepage data retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Homepage data retrieved successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="about_us", type="object", nullable=true,
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="About Our Company"),
 *                     @OA\Property(property="description", type="string", example="We are a leading company..."),
 *                     @OA\Property(property="created_at", type="string", example="2024-08-01T12:00:00Z")
 *                 ),
 *                 @OA\Property(property="services", type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="title", type="string", example="Web Development"),
 *                         @OA\Property(property="is_active", type="boolean", example=true)
 *                     )
 *                 ),
 *                 @OA\Property(property="media", type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="title", type="string", example="Video 1"),
 *                         @OA\Property(property="url", type="string", example="https://example.com/media/video.mp4"),
 *                         @OA\Property(property="is_active", type="boolean", example=true)
 *                     )
 *                 ),
 *                 @OA\Property(property="sliders", type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="image", type="string", example="https://example.com/slider.jpg"),
 *                         @OA\Property(property="title", type="string", example="Welcome to Our Site"),
 *                         @OA\Property(property="is_active", type="boolean", example=true)
 *                     )
 *                 ),
 *                 @OA\Property(property="categories", type="array",
 *                     @OA\Items(
 *                         @OA\Property(property="id", type="integer", example=1),
 *                         @OA\Property(property="name", type="string", example="Technology"),
 *                         @OA\Property(property="is_active", type="boolean", example=true),
 *                         @OA\Property(property="partners", type="array",
 *                             @OA\Items(
 *                                 @OA\Property(property="id", type="integer", example=1),
 *                                 @OA\Property(property="name", type="string", example="Partner 1"),
 *                                 @OA\Property(property="is_active", type="boolean", example=true)
 *                             )
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to load homepage data",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to load homepage data"),
 *             @OA\Property(property="error", type="string", example="Exception message here")
 *         )
 *     )
 * )
 */

    public function index()
    {
        try {
            $about = AboutUs::first();
            $services = Service::where('is_active', true)->latest()->get();
            $media = Media::where('is_active', true)->latest()->get();
            $sliders = Slider::where('is_active', true)->latest()->get();

            $categories = Category::where('is_active', true)
                ->with(['partners' => function ($q) {
                    $q->where('is_active', true);
                }])
                ->latest()->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Homepage data retrieved successfully',
                'data' => [
                    'about_us' => $about,
                    'services' => $services,
                    'categories' => $categories,
                    'media' => $media,
                    'sliders' => $sliders
                ]
            ], 200);

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to load homepage data');
        }
    }

    // Unified exception handling
    private function handleException(Throwable $e, $customMessage = 'An unexpected error occurred')
    {
        return response()->json([
            'status' => 'error',
            'message' => $customMessage,
            'error' => $e->getMessage()
        ], 500);
    }
}
