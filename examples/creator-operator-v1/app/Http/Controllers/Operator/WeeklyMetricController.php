<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\WeeklyMetric;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WeeklyMetricController extends Controller
{
    public function index(Creator $creator): View
    {
        $this->authorize('view', $creator);

        $metrics = $creator->weeklyMetrics()->latest('week_start')->get();

        return view('operator.metrics.index', compact('creator', 'metrics'));
    }

    public function create(Creator $creator): View
    {
        $this->authorize('update', $creator);

        return view('operator.metrics.create', compact('creator'));
    }

    public function store(Request $request, Creator $creator): RedirectResponse
    {
        $this->authorize('update', $creator);

        $validated = $request->validate([
            'week_start' => ['required', 'date'],
            'videos_published' => ['required', 'integer', 'min:0'],
            'best_video_url' => ['nullable', 'url', 'max:500'],
            'best_video_views' => ['nullable', 'integer', 'min:0'],
            'experiment' => ['nullable', 'string', 'max:500'],
            'experiment_result' => ['nullable', 'string', 'max:500'],
            'operator_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        WeeklyMetric::query()->create([
            ...$validated,
            'creator_id' => $creator->id,
        ]);

        return redirect()
            ->route('operator.creators.metrics.index', $creator)
            ->with('status', 'Weekly metrics saved.');
    }
}
