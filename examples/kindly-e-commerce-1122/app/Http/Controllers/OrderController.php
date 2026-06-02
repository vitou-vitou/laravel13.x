<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->with('items.product')
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View|Response
    {
        $user = auth()->user();

        if ($order->user_id !== $user?->id && ! $user?->is_admin) {
            abort(403);
        }

        $order->load('items.product');

        return view('orders.show', compact('order'));
    }
}
