<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // إرسال رسالة تواصل
    /**
 * @OA\Post(
 *     path="/api/contacts",
 *     summary="إرسال رسالة تواصل",
 *     tags={"Contacts"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "message"},
 *             @OA\Property(property="name", type="string", example="أحمد"),
 *             @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *             @OA\Property(property="phone", type="string", example="0123456789"),
 *             @OA\Property(property="message", type="string", example="أرغب في التواصل بشأن الخدمة")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="تم إرسال الرسالة بنجاح",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Message sent successfully"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="فشل إرسال الرسالة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Failed to send message"),
 *             @OA\Property(property="error", type="string", example="Validation or DB error")
 *         )
 *     )
 * )
 */

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
    /**
 * @OA\Get(
 *     path="/api/contacts",
 *     summary="عرض كل الرسائل",
 *     tags={"Contacts"},
 *     @OA\Response(
 *         response=200,
 *         description="تم جلب الرسائل بنجاح",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Messages fetched successfully"),
 *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="فشل جلب الرسائل",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Failed to fetch messages"),
 *             @OA\Property(property="error", type="string", example="DB error")
 *         )
 *     )
 * )
 */

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
    /**
 * @OA\Get(
 *     path="/api/contacts/{id}",
 *     summary="عرض رسالة واحدة",
 *     tags={"Contacts"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="معرّف الرسالة",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="تم جلب الرسالة بنجاح",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Message fetched successfully"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="الرسالة غير موجودة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Message not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="فشل جلب الرسالة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Failed to fetch message"),
 *             @OA\Property(property="error", type="string", example="DB error")
 *         )
 *     )
 * )
 */

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
    /**
 * @OA\Patch(
 *     path="/api/contacts/{id}/seen",
 *     summary="تحديث حالة المشاهدة",
 *     tags={"Contacts"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="معرّف الرسالة",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="تم تحديث حالة المشاهدة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Message marked as seen successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="is_seen", type="boolean", example=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="الرسالة غير موجودة",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Message not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="فشل التحديث",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Failed to mark message as seen"),
 *             @OA\Property(property="error", type="string", example="DB error")
 *         )
 *     )
 * )
 */

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
