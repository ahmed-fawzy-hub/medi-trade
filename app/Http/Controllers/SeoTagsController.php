<?php
namespace App\Http\Controllers;

use App\Models\SeoTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *     schema="SeoTag",
 *     type="object",
 *     title="SeoTag",
 *     required={"en_meta_title", "en_meta_description", "ar_meta_title", "ar_meta_description", "page_name"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="en_meta_title", type="string", example="Welcome to MediTrade"),
 *     @OA\Property(property="en_meta_description", type="string", example="This is the English meta description."),
 *     @OA\Property(property="ar_meta_title", type="string", example="مرحباً بكم في ميدي تريد"),
 *     @OA\Property(property="ar_meta_description", type="string", example="هذا هو الوصف العربي."),
 *     @OA\Property(property="page_name", type="string", example="homepage"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-06T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-06T12:34:56Z"),
 * )
 */


class SeoTagsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/seo-tags",
     *     summary="Get all SEO tags",
     *     tags={"SEO Tags"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SeoTag"))
     *         )
     *     )
     * )
     */
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

     /**
     * @OA\Post(
     *     path="/api/seo-tags",
     *     summary="Create a new SEO tag",
     *     tags={"SEO Tags"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"en_meta_title", "en_meta_description", "ar_meta_title", "ar_meta_description", "page_name"},
     *             @OA\Property(property="en_meta_title", type="string"),
     *             @OA\Property(property="en_meta_description", type="string"),
     *             @OA\Property(property="ar_meta_title", type="string"),
     *             @OA\Property(property="ar_meta_description", type="string"),
     *             @OA\Property(property="page_name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/SeoTag")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/seo-tags/{id}",
     *     summary="Get a specific SEO tag",
     *     tags={"SEO Tags"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="SEO Tag ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Found",
     *         @OA\JsonContent(ref="#/components/schemas/SeoTag")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
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
    /**
     * @OA\Put(
     *     path="/api/seo-tags/{id}",
     *     summary="Update a SEO tag",
     *     tags={"SEO Tags"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="SEO Tag ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"en_meta_title", "en_meta_description", "ar_meta_title", "ar_meta_description", "page_name"},
     *             @OA\Property(property="en_meta_title", type="string"),
     *             @OA\Property(property="en_meta_description", type="string"),
     *             @OA\Property(property="ar_meta_title", type="string"),
     *             @OA\Property(property="ar_meta_description", type="string"),
     *             @OA\Property(property="page_name", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", ref="#/components/schemas/SeoTag")
     *         )
     *     )
     * )
     */

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
