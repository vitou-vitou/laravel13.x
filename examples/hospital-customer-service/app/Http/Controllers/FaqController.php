<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function tree(): JsonResponse
    {
        $faqs = Faq::with('targetDepartment')
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        $tree = $faqs->groupBy('category');

        return response()->json([
            'status' => 'success',
            'data' => $tree,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->query('q', '');

        $faqs = Faq::with('targetDepartment')
            ->where('is_active', true)
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($sub) use ($query) {
                    $sub->where('question', 'like', "%{$query}%")
                        ->orWhere('answer', 'like', "%{$query}%")
                        ->orWhere('category', 'like', "%{$query}%");
                });
            })
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $faqs->count(),
            'data' => $faqs,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'action_type' => 'nullable|string|max:50',
            'target_department_id' => 'nullable|exists:departments,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $faq = Faq::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ created successfully',
            'data' => $faq,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $faq = Faq::findOrFail($id);

        $validated = $request->validate([
            'category' => 'sometimes|required|string|max:255',
            'question' => 'sometimes|required|string|max:255',
            'answer' => 'sometimes|required|string',
            'action_type' => 'nullable|string|max:50',
            'target_department_id' => 'nullable|exists:departments,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $faq->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ updated successfully',
            'data' => $faq,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ deleted successfully',
        ]);
    }
}
