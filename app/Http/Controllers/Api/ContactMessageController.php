<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactMessageController extends Controller
{
    public function store(ContactMessageRequest $request): JsonResponse
    {
        try {
            $message = ContactMessage::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال الرسالة بنجاح',
                'data' => $message
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الرسالة',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
