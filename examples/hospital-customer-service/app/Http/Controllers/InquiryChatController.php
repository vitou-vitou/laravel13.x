<?php

namespace App\Http\Controllers;

use App\Events\InquiryStatusUpdated;
use App\Events\NewInquiryMessage;
use App\Models\Inquiry;
use App\Models\InquiryMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InquiryChatController extends Controller
{
    public function createInquiry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_name' => 'nullable|string|max:255',
            'patient_phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'initial_message' => 'required|string',
        ]);

        $sessionId = (string) Str::uuid();

        $inquiry = Inquiry::create([
            'session_id' => $sessionId,
            'patient_name' => $validated['patient_name'] ?? 'Visitor',
            'patient_phone' => $validated['patient_phone'] ?? null,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'department_id' => $validated['department_id'],
            'status' => 'open',
        ]);

        $message = InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_type' => 'patient',
            'sender_name' => $inquiry->patient_name,
            'message' => $validated['initial_message'],
            'is_read' => false,
        ]);

        event(new NewInquiryMessage($message));

        return response()->json([
            'status' => 'success',
            'message' => 'Inquiry opened successfully',
            'data' => [
                'inquiry' => $inquiry->load('department'),
                'initial_message' => $message,
            ],
        ], 201);
    }

    public function getMessages(int $id): JsonResponse
    {
        $inquiry = Inquiry::with(['department', 'assignedStaff'])->findOrFail($id);

        $messages = $inquiry->messages()->orderBy('id')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'inquiry' => $inquiry,
                'messages' => $messages,
            ],
        ]);
    }

    public function patientReply(Request $request, int $id): JsonResponse
    {
        $inquiry = Inquiry::findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $message = InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_type' => 'patient',
            'sender_name' => $inquiry->patient_name ?? 'Patient',
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $inquiry->update(['status' => 'open']);

        event(new NewInquiryMessage($message));

        return response()->json([
            'status' => 'success',
            'data' => $message,
        ], 201);
    }

    public function staffInbox(Request $request): JsonResponse
    {
        $departmentId = $request->query('department_id');
        $status = $request->query('status');

        $inquiries = Inquiry::with(['department', 'assignedStaff', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_messages_count' => fn ($q) => $q->where('is_read', false)->where('sender_type', 'patient')])
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $inquiries->count(),
            'data' => $inquiries,
        ]);
    }

    public function staffReply(Request $request, int $id): JsonResponse
    {
        $inquiry = Inquiry::findOrFail($id);

        $validated = $request->validate([
            'sender_name' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $inquiry->messages()
            ->where('sender_type', 'patient')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $message = InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_type' => 'staff',
            'sender_name' => $validated['sender_name'],
            'message' => $validated['message'],
            'is_read' => true,
        ]);

        $inquiry->update(['status' => 'waiting_patient']);

        event(new NewInquiryMessage($message));

        return response()->json([
            'status' => 'success',
            'data' => $message,
        ], 201);
    }

    public function reassign(Request $request, int $id): JsonResponse
    {
        $inquiry = Inquiry::findOrFail($id);

        $validated = $request->validate([
            'department_id' => 'sometimes|exists:departments,id',
            'assigned_staff_id' => 'nullable|exists:users,id',
        ]);

        $inquiry->update($validated);
        $inquiry->load(['department', 'assignedStaff']);

        event(new InquiryStatusUpdated($inquiry));

        return response()->json([
            'status' => 'success',
            'message' => 'Inquiry reassigned successfully',
            'data' => $inquiry,
        ]);
    }

    public function resolve(Request $request, int $id): JsonResponse
    {
        $inquiry = Inquiry::findOrFail($id);

        $validated = $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $inquiry->update([
            'status' => 'resolved',
            'resolution_notes' => $validated['resolution_notes'],
            'resolved_at' => now(),
        ]);
        $inquiry->load(['department', 'assignedStaff']);

        event(new InquiryStatusUpdated($inquiry));

        return response()->json([
            'status' => 'success',
            'message' => 'Inquiry resolved successfully',
            'data' => $inquiry,
        ]);
    }
}
