<x-app-layout>
    <x-store-page title="Your cart" max="max-w-4xl">
        <x-flash-status class="mt-6" />

            @if ($lines->isEmpty())
                <div class="store-card mt-6 p-10 text-center">
                    <p class="text-lg font-medium text-stone-900">Your cart is empty</p>
                    <p class="mt-1 text-stone-500">Browse the catalog and add a variant to get started.</p>
                    <a href="{{ route('catalog.index') }}" class="btn-brand mt-6 inline-flex">Browse products</a>
                </div>
            @else
                <div class="store-card mt-6 divide-y divide-stone-100">
                    @foreach ($lines as $line)
                        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-stone-900">{{ $line->variant->product->name }} — {{ $line->variant->name }}</p>
                                <p class="text-sm text-stone-500">{{ $line->variant->product->vendor->store_name }}</p>
                            </div>
                            <form method="POST" action="{{ route('cart.update', $line->variant) }}" class="flex items-center gap-3">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $line->quantity }}" min="0" class="store-input w-24">
                                <button type="submit" class="text-sm font-medium text-brand-600 hover:text-brand-700">Update</button>
                            </form>
                            <p class="font-semibold text-stone-900">${{ number_format($line->lineTotalCents() / 100, 2) }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="store-card mt-6 p-6">
                    <h2 class="font-semibold text-stone-900">Promo code</h2>
                    @if ($appliedPromo)
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg bg-emerald-50 px-4 py-3 text-sm">
                            <span class="font-medium text-emerald-900">{{ $appliedPromo->code }} applied</span>
                            <form method="POST" action="{{ route('cart.promo.remove') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-emerald-800 hover:text-emerald-900">Remove</button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('cart.promo.apply') }}" class="mt-3 flex flex-col gap-2 sm:flex-row">
                            @csrf
                            <input type="text" name="code" placeholder="Enter code" class="store-input flex-1 uppercase" maxlength="40" required>
                            <button type="submit" class="btn-brand sm:shrink-0">Apply</button>
                        </form>
                        @error('code')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    @endif
                </div>

                <div class="store-card mt-6 p-6">
                    <h2 class="font-semibold text-stone-900">Order summary</h2>
                    <div class="mt-4 space-y-2 text-sm text-stone-600">
                        @foreach ($vendorSubtotals as $subtotal)
                            <div class="flex justify-between">
                                <span>{{ $subtotal['vendor_name'] }}</span>
                                <span>${{ number_format($subtotal['subtotal_cents'] / 100, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 space-y-2 border-t border-stone-100 pt-4 text-sm">
                        <div class="flex justify-between text-stone-600">
                            <span>Subtotal</span>
                            <span>${{ number_format($subtotalCents / 100, 2) }}</span>
                        </div>
                        @if ($discountCents > 0)
                            <div class="flex justify-between text-emerald-700">
                                <span>Promo discount</span>
                                <span>−${{ number_format($discountCents / 100, 2) }}</span>
                            </div>
                        @endif
                    </div>
                    <p class="mt-4 border-t border-stone-100 pt-4 text-xl font-bold text-stone-900">
                        Total: ${{ number_format($totalCents / 100, 2) }}
                    </p>

                    @auth
                        @if ($shippingAddresses->isNotEmpty())
                            <div class="mt-6 space-y-2 border-t border-stone-100 pt-4">
                                <p class="text-sm font-medium text-stone-900">Ship to</p>
                                @foreach ($shippingAddresses as $address)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-stone-200 p-3 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50">
                                        <input
                                            type="radio"
                                            name="shipping_address_id"
                                            value="{{ $address->id }}"
                                            class="mt-1"
                                            @checked($address->is_default)
                                            form="checkout-form"
                                            required
                                        >
                                        <span class="text-sm text-stone-700">
                                            <span class="font-medium text-stone-900">{{ $address->label }}</span> — {{ $address->formattedSingleLine() }}
                                        </span>
                                    </label>
                                @endforeach
                                <a href="{{ route('account.addresses.index') }}" class="text-sm text-brand-600 hover:text-brand-700">Manage addresses</a>
                                @error('shipping_address_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @else
                            <p class="mt-6 text-sm text-stone-600">
                                <a href="{{ route('account.addresses.index') }}" class="font-medium text-brand-600 hover:text-brand-700">Add a shipping address</a> (optional for checkout).
                            </p>
                        @endif

                        <div class="mt-6 rounded-lg border border-stone-200 bg-stone-50/80 p-4 text-sm text-stone-600">
                            <p class="font-medium text-stone-900">Buyer protection</p>
                            <p class="mt-1">Pay securely with Stripe. If something goes wrong after delivery, open a dispute from your order page — our team reviews cases and can issue refunds when appropriate.</p>
                        </div>

                        <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}" class="mt-6">
                            @csrf
                            <button type="submit" class="btn-brand">Checkout</button>
                        </form>
                    @else
                        <p class="mt-6 text-sm text-stone-600">
                            <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Log in</a> to checkout.
                        </p>
                    @endauth
                </div>
            @endif
    </x-store-page>
</x-app-layout>
