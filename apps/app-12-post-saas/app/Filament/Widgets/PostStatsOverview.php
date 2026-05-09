<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Support\WorkspaceContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PostStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $workspaceId = WorkspaceContext::id();
        $base = Post::query()
            ->when($workspaceId, fn ($q) => $q->where('workspace_id', $workspaceId))
            ->when(! $workspaceId, fn ($q) => $q->whereRaw('1 = 0'));

        return [
            Stat::make('Posts', (clone $base)->count()),
            Stat::make('Published', (clone $base)->where('status', Post::STATUS_PUBLISHED)->count()),
            Stat::make('Drafts', (clone $base)->where('status', Post::STATUS_DRAFT)->count()),
        ];
    }
}
