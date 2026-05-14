<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index()
    {
        return response()->json([
            'todos' => Todo::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $todo = Todo::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'user_id'     => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Todo created successfully',
            'todo'    => $todo,
        ], 201);
    }
}
