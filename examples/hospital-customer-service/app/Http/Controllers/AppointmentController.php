<?php

namespace App\Http\Controllers;

use App\Models\AppointmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:50',
            'patient_email' => 'nullable|email|max:255',
            'preferred_doctor' => 'nullable|string|max:255',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time_slot' => 'required|in:morning,afternoon',
        ]);

        $trackingCode = AppointmentRequest::generateTrackingCode();

        $appointment = AppointmentRequest::create(array_merge($validated, [
            'tracking_code' => $trackingCode,
            'status' => 'pending',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment request submitted successfully',
            'data' => [
                'tracking_code' => $appointment->tracking_code,
                'status' => $appointment->status,
                'department_id' => $appointment->department_id,
                'preferred_date' => $appointment->preferred_date->toDateString(),
                'preferred_time_slot' => $appointment->preferred_time_slot,
            ],
        ], 201);
    }

    public function show(string $trackingCode): JsonResponse
    {
        $appointment = AppointmentRequest::with('department')
            ->where('tracking_code', $trackingCode)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'tracking_code' => $appointment->tracking_code,
                'department' => $appointment->department->name,
                'status' => $appointment->status,
                'patient_name' => $appointment->patient_name,
                'preferred_date' => $appointment->preferred_date->toDateString(),
                'preferred_time_slot' => $appointment->preferred_time_slot,
                'confirmed_date' => $appointment->confirmed_date?->toDateString(),
                'confirmed_time_slot' => $appointment->confirmed_time_slot,
                'assigned_room' => $appointment->assigned_room,
                'staff_notes' => $appointment->staff_notes,
            ],
        ]);
    }

    public function staffIndex(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $departmentId = $request->query('department_id');

        $appointments = AppointmentRequest::with('department')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $appointments->count(),
            'data' => $appointments,
        ]);
    }

    public function confirm(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'confirmed_date' => 'required|date',
            'confirmed_time_slot' => 'required|string|max:50',
            'assigned_room' => 'required|string|max:100',
            'staff_notes' => 'nullable|string',
        ]);

        $appointment = AppointmentRequest::findOrFail($id);
        $appointment->update(array_merge($validated, [
            'status' => 'confirmed',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment confirmed successfully',
            'data' => $appointment,
        ]);
    }

    public function reschedule(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'confirmed_date' => 'required|date',
            'confirmed_time_slot' => 'required|string|max:50',
            'staff_notes' => 'required|string',
        ]);

        $appointment = AppointmentRequest::findOrFail($id);
        $appointment->update(array_merge($validated, [
            'status' => 'rescheduled',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment rescheduled and proposed to patient',
            'data' => $appointment,
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'staff_notes' => 'nullable|string',
        ]);

        $appointment = AppointmentRequest::findOrFail($id);
        $appointment->update([
            'status' => 'cancelled',
            'staff_notes' => $validated['staff_notes'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Appointment cancelled',
            'data' => $appointment,
        ]);
    }
}
