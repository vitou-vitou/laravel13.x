<?php
namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Issue;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Issues', Issue::count())
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary'),
            Stat::make('In Progress', Issue::where('status','in_progress')->count())
                ->icon('heroicon-o-arrow-path')
                ->color('warning'),
            Stat::make('Done', Issue::where('status','done')->count())
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Bugs', Issue::where('type','bug')->count())
                ->icon('heroicon-o-bug-ant')
                ->color('danger'),
        ];
    }
}