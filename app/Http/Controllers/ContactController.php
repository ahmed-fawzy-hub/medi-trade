<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // إرسال رسالة تواصل
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'message' => 'required|string|max:2000',
            ]);

            $contact = Contact::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Message sent successfully',
                'data' => $contact,
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // عرض كل الرسائل
    public function index()
    {
        try {
            $contacts = Contact::latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Messages fetched successfully',
                'data' => $contacts,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch messages',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // عرض رسالة واحدة
    public function show($id)
    {
        try {
            $contact = Contact::find($id);

            if (!$contact) {
                return response()->json([
                    'status' => false,
                    'message' => 'Message not found',
                ], 404);
            }

            $contact->update(['is_read' => true]);

            return response()->json([
                'status' => true,
                'message' => 'Message fetched successfully',
                'data' => $contact,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // تحديث حالة المشاهدة
    public function markAsSeen($id)
    {
        try {
            $contact = Contact::find($id);

            if (!$contact) {
                return response()->json([
                    'status' => false,
                    'message' => 'Message not found',
                ], 404);
            }

            $contact->is_seen = true;
            $contact->save();

            return response()->json([
                'status' => true,
                'message' => 'Message marked as seen successfully',
                'data' => [
                    'id' => $contact->id,
                    'is_seen' => $contact->is_seen,
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to mark message as seen',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
