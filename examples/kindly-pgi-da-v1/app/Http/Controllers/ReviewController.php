<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ReviewController extends Controller implements HasMiddleware
{
    public function __construct(private ReviewService $reviews) {}

    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function create(Order $order, Product $product): View
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return view('reviews.create', compact('order', 'product'));
    }

    public function store(Request $request, Order $order, Product $product): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $this->reviews->create(
            auth()->user(),
            $product,
            $order,
            (int) $validated['rating'],
            $validated['body'],
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Review submitted.');
    }
}
