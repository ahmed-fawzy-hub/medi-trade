<?php

namespace App\Http\Controllers;

use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;

class PrivacyPolicyeController extends Controller
{
    // GET /api/privacy-policy
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

    // POST or PUT /api/privacy-policy
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
