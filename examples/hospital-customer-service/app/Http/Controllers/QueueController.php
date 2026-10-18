<?php

namespace App\Http\Controllers;

use App\Events\QueueTokenUpdated;
use App\Models\Department;
use App\Models\QueueToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'patient_name' => 'nullable|string|max:255',
            'patient_phone' => 'nullable|string|max:50',
        ]);

        $department = Department::findOrFail($validated['department_id']);
        $tokenNumber = QueueToken::generateNextTokenNumber($department);

        $token = QueueToken::create([
            'department_id' => $department->id,
            'token_number' => $tokenNumber,
            'patient_name' => $validated['patient_name'] ?? null,
            'patient_phone' => $validated['patient_phone'] ?? null,
            'status' => 'waiting',
        ]);

        event(new QueueTokenUpdated($token));

        return response()->json([
            'status' => 'success',
            'message' => 'Queue token issued successfully',
            'data' => [
                'token_number' => $token->token_number,
                'department' => $department->name,
                'status' => $token->status,
                'position_ahead' => $token->calculatePositionAhead(),
                'estimated_wait_minutes' => $token->estimatedWaitMinutes(),
                'created_at' => $token->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function show(string $tokenNumber): JsonResponse
    {
        $token = QueueToken::with('department')
            ->where('token_number', $tokenNumber)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'token_number' => $token->token_number,
                'department' => $token->department->name,
                'status' => $token->status,
                'counter_assigned' => $token->counter_assigned,
                'position_ahead' => $token->calculatePositionAhead(),
                'estimated_wait_minutes' => $token->estimatedWaitMinutes(),
                'called_at' => $token->called_at?->toIso8601String(),
            ],
        ]);
    }

    public function staffQueue(Request $request): JsonResponse
    {
        $departmentId = $request->query('department_id');

        $tokens = QueueToken::with('department')
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $tokens->count(),
            'data' => $tokens,
        ]);
    }

    public function callNext(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'counter' => 'required|string|max:50',
        ]);

        $token = QueueToken::findOrFail($id);
        $token->update([
            'status' => 'called',
            'counter_assigned' => $validated['counter'],
            'called_at' => now(),
        ]);

        event(new QueueTokenUpdated($token));

        return response()->json([
            'status' => 'success',
            'message' => 'Patient called to counter',
            'data' => $token,
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:serving,completed,no_show',
        ]);

        $token = QueueToken::findOrFail($id);
        $updates = ['status' => $validated['status']];

        if ($validated['status'] === 'serving') {
            $updates['served_at'] = now();
        } elseif ($validated['status'] === 'completed') {
            $updates['completed_at'] = now();
        }

        $token->update($updates);
        event(new QueueTokenUpdated($token));

        return response()->json([
            'status' => 'success',
            'message' => "Token status updated to {$validated['status']}",
            'data' => $token,
        ]);
    }
}
