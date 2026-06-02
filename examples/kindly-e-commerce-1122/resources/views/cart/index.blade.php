@extends('layouts.shop')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Your cart</h1>

    @if ($lines->isEmpty())
        <p class="text-gray-600">Your cart is empty. <a href="{{ route('shop.index') }}" class="underline">Continue shopping</a>.</p>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-3">Product</th>
                        <th class="px-4 py-3">Price</th>
                        <th class="px-4 py-3">Qty</th>
                        <th class="px-4 py-3">Line total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lines as $line)
                        <tr class="border-t">
                            <td class="px-4 py-3">{{ $line['product']->name }}</td>
                            <td class="px-4 py-3">{{ $line['product']->formattedPrice() }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('cart.update', $line['product']) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="99" class="w-16 rounded border-gray-300">
                                    <button type="submit" class="text-xs underline">Update</button>
                                </form>
                            </td>
                            <td class="px-4 py-3">${{ number_format($line['line_total_cents'] / 100, 2) }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('cart.destroy', $line['product']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-xs underline">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow p-4 max-w-md">
            <h2 class="font-semibold mb-3">Coupon</h2>
            @if ($appliedCoupon)
                <p class="text-sm text-gray-600 mb-2">Applied: <strong>{{ $appliedCoupon }}</strong></p>
                <form method="POST" action="{{ route('cart.coupon.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm underline text-red-600">Remove coupon</button>
                </form>
            @else
                <form method="POST" action="{{ route('cart.coupon.store') }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="code" placeholder="e.g. KINDLY10" class="flex-1 rounded border-gray-300 text-sm" required>
                    <button type="submit" class="bg-gray-800 text-white rounded-md px-4 py-2 text-sm">Apply</button>
                </form>
            @endif
        </div>

        <div class="mt-6 space-y-1 text-sm">
            <p>Subtotal: ${{ number_format($subtotalCents / 100, 2) }}</p>
            @if ($discountCents > 0)
                <p class="text-green-700">Discount: −${{ number_format($discountCents / 100, 2) }}</p>
            @endif
            <p class="text-lg font-semibold">Total: ${{ number_format($totalCents / 100, 2) }}</p>
        </div>

        <div class="mt-4">
            @auth
                <form method="POST" action="{{ route('checkout.store') }}">
                    @csrf
                    <button type="submit" class="bg-gray-800 text-white rounded-md px-6 py-2 text-sm hover:bg-gray-700">
                        Pay with Stripe
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-block bg-gray-800 text-white rounded-md px-6 py-2 text-sm hover:bg-gray-700">
                    Log in to checkout
                </a>
            @endauth
        </div>
    @endif
@endsection
