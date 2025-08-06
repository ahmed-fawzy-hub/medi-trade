<?php

namespace App\Http\Controllers;

use App\Models\ContactInfo;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Validation\ValidationException;

class ContactInfoController extends Controller
{
    // Show contact information
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
