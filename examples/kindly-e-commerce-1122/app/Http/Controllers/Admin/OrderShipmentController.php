<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderLifecycleService;
use Illuminate\Http\RedirectResponse;

class OrderShipmentController extends Controller
{
    public function store(Order $order, OrderLifecycleService $lifecycle): RedirectResponse
    {
        if (! $lifecycle->markShipped($order)) {
            return redirect()
                ->route('orders.show', $order)
                ->with('status', 'Only paid orders can be marked as shipped.');
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Order marked as shipped.');
    }
}
