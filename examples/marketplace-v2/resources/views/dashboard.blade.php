<x-app-layout>
    <x-store-page title="Dashboard">
        <div class="store-panel mt-6 space-y-4">
            <p class="text-stone-700">
                You're logged in as <strong>{{ auth()->user()->name }}</strong>
                <span class="text-stone-500">({{ auth()->user()->role->value }})</span>
            </p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('catalog.index') }}" class="admin-subnav-link">Shop</a>
                <a href="{{ route('cart.index') }}" class="admin-subnav-link">Cart</a>
                <a href="{{ route('orders.index') }}" class="admin-subnav-link">Orders</a>
                @if (! auth()->user()->isVendor())
                    <a href="{{ route('vendor.apply') }}" class="admin-subnav-link">Apply to sell</a>
                @endif
                @if (auth()->user()->isVendor() && auth()->user()->vendor)
                    <a href="{{ route('vendor.dashboard') }}" class="admin-subnav-link">Vendor dashboard</a>
                @endif
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="admin-subnav-link">Admin</a>
                @endif
            </div>
        </div>
    </x-store-page>
</x-app-layout>
