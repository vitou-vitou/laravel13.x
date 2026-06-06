<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Analytics Dashboard') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}
                </p>
            </div>
            <a href="{{ url('/admin/orders') }}"
               class="inline-flex items-center justify-center rounded-md bg-gray-800 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                {{ __('Manage Orders') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if (session('checkout_order_id'))
                @php
                    $checkoutNotification = [
                        'orderId' => session('checkout_order_id'),
                        'customer' => session('checkout_customer'),
                        'amount' => session('checkout_total'),
                    ];
                @endphp
                <script type="application/json" id="checkout-order-notification">@json($checkoutNotification)</script>
                <div class="rounded-md bg-green-50 dark:bg-green-950/40 p-4 text-sm text-green-800 dark:text-green-200">
                    {{ __('Order #:id placed successfully for :total.', [
                        'id' => session('checkout_order_id'),
                        'total' => session('checkout_total'),
                    ]) }}
                </div>
            @endif

            <livewire:dashboard-metrics />

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Revenue Trend') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Paid revenue over the last 7 days') }}</p>
                        <div class="mt-6 h-64">
                            <canvas id="revenue-trend-chart" aria-label="Revenue trend chart" role="img"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Order Status') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Orders grouped by status') }}</p>
                        <div class="mt-6 h-64 flex items-center justify-center">
                            <canvas id="order-status-chart" aria-label="Order status chart" role="img"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <script type="application/json" id="dashboard-charts-data">@json($chartData)</script>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/order-notifications.js', 'resources/js/dashboard-charts.js'])
    @endpush
</x-app-layout>
