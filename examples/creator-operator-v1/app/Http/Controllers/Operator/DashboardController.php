<?php

namespace App\Http\Controllers\Operator;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
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
                ->get(),
        ]);
    }
}
