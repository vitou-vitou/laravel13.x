<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Support\WorkspaceContext;
use Illuminate\Support\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $workspaceId = WorkspaceContext::id();
        $taskQuery = Task::query()
            ->when($workspaceId, fn ($query) => $query->where('workspace_id', $workspaceId))
            ->when(! $workspaceId, fn ($query) => $query->whereRaw('1 = 0'));

        $overdue = (clone $taskQuery)
            ->whereDate('due_date', '<', Carbon::today())
            ->where('status', '!=', 'done')
            ->count();
        $dueThisWeek = (clone $taskQuery)
            ->whereBetween('due_date', [Carbon::today(), Carbon::today()->copy()->endOfWeek()])
            ->count();

        return [
            Stat::make('Total tasks', (clone $taskQuery)->count()),
            Stat::make('Overdue tasks', $overdue),
            Stat::make('Due this week', $dueThisWeek),
        ];
    }
}
