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
