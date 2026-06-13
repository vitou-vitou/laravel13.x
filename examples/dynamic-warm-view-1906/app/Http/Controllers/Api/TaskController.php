<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()
            ->tasks()
            ->latest()
            ->get();

        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $task = $request->user()->tasks()->create($validated);

        return response()->json($task->fresh(), 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json($task);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $task->update($validated);

        return response()->json($task->fresh());
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }
}
