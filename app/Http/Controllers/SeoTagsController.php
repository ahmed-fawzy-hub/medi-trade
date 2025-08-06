<?php
namespace App\Http\Controllers;

use App\Models\SeoTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SeoTagsController extends Controller
{
    public function index()
    {
        try {
            $seoTags = SeoTag::latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'SEO Tags fetched successfully.',
                'data' => $seoTags,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('SEO Tags fetch error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching SEO Tags.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'en_meta_title' => 'required|string|max:255',
                'en_meta_description' => 'required|string',
                'ar_meta_title' => 'required|string|max:255',
                'ar_meta_description' => 'required|string',
                'page_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $seoTag = SeoTag::create($request->all());

            return response()->json([
                'status' => true,
                'message' => 'SEO Tag created successfully.',
                'data' => $seoTag,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('SEO Tag creation error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the SEO Tag.',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $seoTag = SeoTag::findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'SEO Tag fetched successfully.',
                'data' => $seoTag,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('SEO Tag fetch error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'SEO Tag not found.',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'en_meta_title' => 'required|string|max:255',
                'en_meta_description' => 'required|string',
                'ar_meta_title' => 'required|string|max:255',
                'ar_meta_description' => 'required|string',
                'page_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $seoTag = SeoTag::findOrFail($id);
            $seoTag->update($request->all());

            return response()->json([
                'status' => true,
                'message' => 'SEO Tag updated successfully.',
                'data' => $seoTag,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('SEO Tag update error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the SEO Tag.',
            ], 500);
        }
    }
}
