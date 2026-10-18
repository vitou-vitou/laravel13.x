<?php

namespace App\Http\Controllers;

use App\Models\ReportStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportStatusController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:50',
            'phone_last_four' => 'required|string|size:4',
        ]);

        $report = ReportStatus::with('department')
            ->where('order_number', $validated['order_number'])
            ->where('phone_last_four', $validated['phone_last_four'])
            ->first();

        if (! $report) {
            return response()->json([
                'status' => 'error',
                'message' => 'No matching test order found for the provided reference and phone verification.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'order_number' => $report->order_number,
                'department' => $report->department->name,
                'test_category' => $report->test_category,
                'status' => $report->status,
                'pickup_counter' => $report->pickup_counter,
                'ready_at' => $report->ready_at?->toIso8601String(),
                'collected_at' => $report->collected_at?->toIso8601String(),
                'privacy_notice' => 'Clinical measurements and diagnoses are never displayed online. Please present photo ID at the designated counter to collect your sealed paper reports.',
            ],
        ]);
    }

    public function staffIndex(Request $request): JsonResponse
    {
        $order = $request->query('order_number');
        $status = $request->query('status');

        $reports = ReportStatus::with('department')
            ->when($order, fn ($q) => $q->where('order_number', 'like', "%{$order}%"))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $reports->count(),
            'data' => $reports,
        ]);
    }

    public function staffStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:50|unique:report_statuses,order_number',
            'phone_last_four' => 'required|string|size:4',
            'department_id' => 'required|exists:departments,id',
            'test_category' => 'required|string|max:255',
            'status' => 'nullable|in:in_analysis,ready_for_pickup,collected',
            'pickup_counter' => 'nullable|string|max:255',
        ]);

        $report = ReportStatus::create(array_merge($validated, [
            'status' => $validated['status'] ?? 'in_analysis',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Report status record created',
            'data' => $report,
        ], 201);
    }

    public function staffUpdate(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:in_analysis,ready_for_pickup,collected',
            'pickup_counter' => 'nullable|string|max:255',
        ]);

        $report = ReportStatus::findOrFail($id);
        $updates = [
            'status' => $validated['status'],
            'pickup_counter' => $validated['pickup_counter'] ?? $report->pickup_counter,
        ];

        if ($validated['status'] === 'ready_for_pickup' && ! $report->ready_at) {
            $updates['ready_at'] = now();
        } elseif ($validated['status'] === 'collected') {
            $updates['collected_at'] = now();
        }

        $report->update($updates);

        return response()->json([
            'status' => 'success',
            'message' => 'Report status updated',
            'data' => $report,
        ]);
    }
}
