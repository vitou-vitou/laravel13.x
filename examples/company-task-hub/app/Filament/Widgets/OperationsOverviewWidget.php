<?php

namespace App\Filament\Widgets;

use App\Enums\IssueSeverity;
use App\Enums\TaskStatus;
use App\Models\CompanyTask;
use App\Models\CustomerTask;
use App\Models\DailyIssueTask;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $pendingCompanyTasks = CompanyTask::where('status', TaskStatus::Pending)->count();
        $criticalOpenIssues = DailyIssueTask::where('severity', IssueSeverity::Critical)
            ->where('status', '!=', TaskStatus::Completed)
            ->count();
        $openCustomerTasks = CustomerTask::where('status', '!=', TaskStatus::Completed)->count();

        return [
            Stat::make('Pending Company Tasks', $pendingCompanyTasks)
                ->description('Internal ops awaiting action')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('Critical Daily Issues', $criticalOpenIssues)
                ->description('Unresolved critical incidents')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($criticalOpenIssues > 0 ? 'danger' : 'success'),

            Stat::make('Active Customer Tasks', $openCustomerTasks)
                ->description('Pending customer deliverables')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}
