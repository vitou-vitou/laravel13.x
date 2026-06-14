<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with(['user', 'groups.vendor', 'payment'])
            ->latest()
            ->paginate(20);

        $openDisputes = \App\Models\Dispute::query()
            ->whereIn('status', [
                \App\Enums\DisputeStatus::Opened,
                \App\Enums\DisputeStatus::UnderReview,
                \App\Enums\DisputeStatus::Escalated,
            ])
            ->count();

        return view('admin.dashboard', compact('orders', 'openDisputes'));
    }

    public function audit(): View
    {
        $logs = PaymentAuditLog::query()
            ->with('payment.order')
            ->latest()
            ->paginate(30);

        return view('admin.audit.index', compact('logs'));
    }
}
