<?php

namespace App\Http\Controllers;

use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;

class PrivacyPolicyeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/privacy-policy",
     *     summary="Get the privacy policy",
     *     description="Retrieve the current privacy policy (English and Arabic).",
     *     tags={"Privacy Policy"},
     *     @OA\Response(
     *         response=200,
     *         description="Privacy policy fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Privacy Policy fetched successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="en_title", type="string", example="Privacy Policy"),
     *                 @OA\Property(property="en_description", type="string", example="This is the English privacy policy."),
     *                 @OA\Property(property="ar_title", type="string", example="سياسة الخصوصية"),
     *                 @OA\Property(property="ar_description", type="string", example="هذه هي سياسة الخصوصية باللغة العربية."),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Something went wrong"),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $policy = PrivacyPolicy::first();

            return response()->json([
                'message' => 'Privacy Policy fetched successfully',
                'data' => $policy
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/privacy-policy",
     *     summary="Create or update the privacy policy",
     *     description="Update or create a new privacy policy with English and Arabic content.",
     *     tags={"Privacy Policy"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"en_title", "en_description", "ar_title", "ar_description"},
     *             @OA\Property(property="en_title", type="string", example="Privacy Policy"),
     *             @OA\Property(property="en_description", type="string", example="English description here"),
     *             @OA\Property(property="ar_title", type="string", example="سياسة الخصوصية"),
     *             @OA\Property(property="ar_description", type="string", example="الوصف العربي هنا")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Privacy policy saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Privacy Policy saved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Something went wrong"),
     *             @OA\Property(property="message", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'en_title' => 'required|string|max:255',
                'en_description' => 'required|string',
                'ar_title' => 'required|string|max:255',
                'ar_description' => 'required|string',
            ]);

            $policy = PrivacyPolicy::first();

            $policy = $policy
                ? tap($policy)->update($validated)
                : PrivacyPolicy::create($validated);

            return response()->json([
                'message' => 'Privacy Policy saved successfully',
                'data' => $policy
            ]);
        } catch (\Throwable $e) {
            $statusCode = $e instanceof \Illuminate\Validation\ValidationException ? 422 : 500;

            return response()->json([
                'error' => $e instanceof \Illuminate\Validation\ValidationException ? 'Validation failed' : 'Something went wrong',
                'message' => $e->getMessage(),
                'errors' => method_exists($e, 'errors') ? $e->errors() : null
            ], $statusCode);
        }
    }
}
