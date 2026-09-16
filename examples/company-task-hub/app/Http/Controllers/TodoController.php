<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Resources\TodoCollection;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * Display a listing of todos.
     */
    public function index(Request $request): TodoCollection
    {
        $query = Todo::query();

        // Apply filters
        if ($request->has('status') && in_array($request->status, Todo::getStatusOptions())) {
            $query->byStatus($request->status);
        }

        if ($request->has('priority') && in_array($request->priority, Todo::getPriorityOptions())) {
            $query->byPriority($request->priority);
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        if (in_array($sortField, ['created_at', 'updated_at', 'due_date', 'priority'])) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        return new TodoCollection(
            $query->paginate($request->get('per_page', 15))
        );
    }

    /**
     * Store a newly created todo.
     */
    public function store(StoreTodoRequest $request): JsonResponse
    {
        $todo = Todo::create($request->validated());

        return (new TodoResource($todo))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified todo.
     */
    public function show(Todo $todo): TodoResource
    {
        return new TodoResource($todo);
    }

    /**
     * Update the specified todo.
     */
    public function update(UpdateTodoRequest $request, Todo $todo): TodoResource
    {
        $todo->update($request->validated());

        return new TodoResource($todo);
    }

    /**
     * Remove the specified todo.
     */
    public function destroy(Todo $todo): JsonResponse
    {
        $todo->delete();

        return response()->json([], 204);
    }

    /**
     * Mark the specified todo as complete.
     */
    public function complete(Todo $todo): TodoResource
    {
        $todo->markComplete();

        return new TodoResource($todo);
    }

    /**
     * Mark the specified todo as incomplete.
     */
    public function incomplete(Todo $todo): TodoResource
    {
        $todo->markIncomplete();

        return new TodoResource($todo);
    }
}