<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(Order $order): View
    {
        $order->load(['user', 'payment.refunds', 'payment.auditLogs', 'groups.vendor', 'promoCode']);

        return view('admin.orders.show', [
            'order' => $order,
            'refundableCents' => $order->payment
                ? app(RefundService::class)->refundableCents($order->payment)
                : 0,
        ]);
    }

    public function refund(Request $request, Order $order, RefundService $refunds): RedirectResponse
    {
        $validated = $request->validate([
            'amount_cents' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $refunds->issue(
            $order,
            (int) $validated['amount_cents'],
            $validated['reason'],
            $request->user(),
        );

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Refund issued.');
    }
}
