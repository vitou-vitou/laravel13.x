<?php

namespace App\Services;

use App\Enums\PublishStatus;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use Illuminate\Support\Collection;

class OperatorDashboardCharts
{
    /**
     * @return array<int, array{label: string, date: string, value: int}>
     */
    public function publishVelocityLast7Days(): array
    {
        $start = now()->subDays(6)->startOfDay();

        $counts = PublishLogEntry::query()
            ->where('status', PublishStatus::Published)
            ->where('posted_time', '>=', $start)
            ->selectRaw('DATE(posted_time) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->daySeries($counts);
    }

    /**
     * New rows entering pending approval (by logged_on).
     *
     * @return array<int, array{label: string, date: string, value: int}>
     */
    public function pendingQueueTrendLast7Days(): array
    {
        $start = now()->subDays(6)->startOfDay();

        $counts = PublishLogEntry::query()
            ->where('status', PublishStatus::PendingApproval)
            ->where('logged_on', '>=', $start->toDateString())
            ->selectRaw('logged_on as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->daySeries($counts);
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function pendingByCreator(): array
    {
        return Creator::query()
            ->withCount(['publishLogEntries as pending_count' => function ($query): void {
                $query->where('status', PublishStatus::PendingApproval);
            }])
            ->whereHas('publishLogEntries', function ($query): void {
                $query->where('status', PublishStatus::PendingApproval);
            })
            ->orderByDesc('pending_count')
            ->limit(8)
            ->get()
            ->map(fn (Creator $creator): array => [
                'label' => '@'.$creator->handle,
                'value' => (int) $creator->pending_count,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<string, mixed>  $counts
     * @return array<int, array{label: string, date: string, value: int}>
     */
    private function daySeries(Collection $counts): array
    {
        $series = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $key = $day->toDateString();

            $series[] = [
                'label' => $day->format('D'),
                'date' => $key,
                'value' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $series;
    }
}
