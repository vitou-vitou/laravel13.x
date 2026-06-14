<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class OrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index(): View
    {
        $orders = auth()->user()
            ->orders()
            ->with(['groups.vendor', 'payment'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_unless($order->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        $order->load(['groups.lines.variant.product', 'groups.vendor', 'groups.dispute', 'payment']);

        return view('orders.show', compact('order'));
    }
}
