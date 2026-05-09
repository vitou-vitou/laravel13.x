<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Support\WorkspaceContext;
use Filament\Widgets\ChartWidget;

class TasksByStatusChart extends ChartWidget
{
    protected ?string $heading = 'Tasks by Status';

    protected function getData(): array
    {
        $workspaceId = WorkspaceContext::id();
        $counts = Task::query()
            ->when($workspaceId, fn ($query) => $query->where('workspace_id', $workspaceId))
            ->when(! $workspaceId, fn ($query) => $query->whereRaw('1 = 0'))
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'datasets' => [[
                'label' => 'Tasks',
                'data' => [
                    (int) ($counts['todo'] ?? 0),
                    (int) ($counts['in_progress'] ?? 0),
                    (int) ($counts['done'] ?? 0),
                ],
            ]],
            'labels' => ['Todo', 'In Progress', 'Done'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
