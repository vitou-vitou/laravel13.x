<?php

namespace App\Http\Controllers\Operator;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use App\Services\OperatorDashboardCharts;
use App\Services\TikTokThumbnailService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected OperatorDashboardCharts $charts,
    ) {}

    public function __invoke(): View
    {
        return view('operator.dashboard', [
            'creatorsCount' => Creator::query()->count(),
            'pendingCount' => PublishLogEntry::query()
                ->where('status', PublishStatus::PendingApproval)
                ->count(),
            'approvedCount' => PublishLogEntry::query()
                ->where('status', PublishStatus::Approved)
                ->count(),
            'publishedThisWeek' => PublishLogEntry::query()
                ->where('status', PublishStatus::Published)
                ->where('posted_time', '>=', now()->subDays(7))
                ->count(),
            'recentEntries' => PublishLogEntry::query()
                ->with('creator')
                ->latest()
                ->limit(10)
                ->get()
                ->tap(function ($entries): void {
                    $thumbService = app(TikTokThumbnailService::class);
                    foreach ($entries as $entry) {
                        if (blank($entry->tiktok_thumbnail_url) && filled($entry->tiktok_url)) {
                            $thumbService->hydrateEntry($entry);
                            $entry->refresh();
                        }
                    }
                }),
            'publishVelocity' => $this->charts->publishVelocityLast7Days(),
            'pendingTrend' => $this->charts->pendingQueueTrendLast7Days(),
            'pendingByCreator' => $this->charts->pendingByCreator(),
        ]);
    }
}
