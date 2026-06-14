<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-900 space-y-2">
                <p>You're logged in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->role->value }}).</p>
                <p><a href="{{ route('catalog.index') }}" class="underline">Browse shop</a> · <a href="{{ route('cart.index') }}" class="underline">Cart</a> · <a href="{{ route('orders.index') }}" class="underline">Orders</a></p>
                @if (!auth()->user()->isVendor())
                    <p><a href="{{ route('vendor.apply') }}" class="underline">Apply to sell</a></p>
                @endif
                @if (auth()->user()->isVendor() && auth()->user()->vendor)
                    <p><a href="{{ route('vendor.dashboard') }}" class="underline">Vendor dashboard</a></p>
                @endif
                @if (auth()->user()->isAdmin())
                    <p><a href="{{ route('admin.vendors.index') }}" class="underline">Admin: vendors</a></p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
