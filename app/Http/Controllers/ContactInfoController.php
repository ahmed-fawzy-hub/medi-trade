<?php

namespace App\Http\Controllers;

use App\Models\ContactInfo;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Validation\ValidationException;

class ContactInfoController extends Controller
{
    // Show contact information
    /**
     * @OA\Get(
     *     path="/api/contact-info",
     *     summary="Get contact information",
     *     tags={"Contact Info"},
     *     @OA\Response(
     *         response=200,
     *         description="Contact information retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Contact information retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="phone_one", type="string", example="0123456789"),
     *                 @OA\Property(property="phone_two", type="string", example=""),
     *                 @OA\Property(property="whatsapp", type="string", example="0123456789"),
     *                 @OA\Property(property="address", type="string", example="Cairo, Egypt"),
     *                 @OA\Property(property="map_link", type="string", example="https://maps.google.com/..."),
     *                 @OA\Property(property="facebook", type="string", example="https://facebook.com/example"),
     *                 @OA\Property(property="instagram", type="string", example="https://instagram.com/example"),
     *                 @OA\Property(property="twitter", type="string", example="https://twitter.com/example")
     *             )
     *         )
     *     )
     * )
     */
    public function show()
    {
        try {
            $contact = ContactInfo::first();

            return response()->json([
                'status' => 'success',
                'message' => 'Contact information retrieved successfully',
                'data' => $contact,
            ], 200);

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to retrieve contact information');
        }
    }

    // Update or create contact information
    /**
     * @OA\Post(
     *     path="/api/contact-info",
     *     summary="Update or create contact information",
     *     tags={"Contact Info"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone_one", "whatsapp", "address"},
     *             @OA\Property(property="phone_one", type="string", example="0123456789"),
     *             @OA\Property(property="phone_two", type="string", example=""),
     *             @OA\Property(property="whatsapp", type="string", example="0123456789"),
     *             @OA\Property(property="address", type="string", example="123 Street, City"),
     *             @OA\Property(property="map_link", type="string", example="https://maps.google.com/..."),
     *             @OA\Property(property="working_hours", type="string", example="Mon-Fri 9am-5pm"),
     *             @OA\Property(property="facebook", type="string", example="https://facebook.com/example"),
     *             @OA\Property(property="instagram", type="string", example="https://instagram.com/example"),
     *             @OA\Property(property="twitter", type="string", example="https://twitter.com/example"),
     *             @OA\Property(property="snapchat", type="string", example="https://snapchat.com/example"),
     *             @OA\Property(property="youtube", type="string", example="https://youtube.com/example"),
     *             @OA\Property(property="tiktok", type="string", example="https://tiktok.com/@example")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact information saved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Contact information saved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="phone_one", type="string", example="0123456789"),
     *                 @OA\Property(property="address", type="string", example="Cairo, Egypt"),
     *                 @OA\Property(property="facebook", type="string", example="https://facebook.com/example")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="fail"),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to save contact information"),
     *             @OA\Property(property="error", type="string", example="Exception message here")
     *         )
     *     )
     * )
     */
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone_one' => 'required|string|max:255',
                'phone_two' => 'nullable|string|max:255',
                'whatsapp' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'map_link' => 'nullable|string|max:255',
                'working_hours' => 'nullable|string|max:255',
                'facebook' => 'nullable|string|max:255',
                'instagram' => 'nullable|string|max:255',
                'twitter' => 'nullable|string|max:255',
                'snapchat' => 'nullable|string|max:255',
                'youtube' => 'nullable|string|max:255',
                'tiktok' => 'nullable|string|max:255',
            ]);

            $contact = ContactInfo::first();

            if (!$contact) {
                $contact = ContactInfo::create($validated);
            } else {
                $contact->update($validated);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Contact information saved successfully',
                'data' => $contact
            ], 200);

        } catch (Throwable $e) {
            return $this->handleException($e, 'Failed to save contact information');
        }
    }

    // Unified exception handling
    private function handleException(Throwable $e, $customMessage = 'An unexpected error occurred')
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        }

        return response()->json([
            'status' => 'error',
            'message' => $customMessage,
            'error' => $e->getMessage()
        ], 500);
    }
}
