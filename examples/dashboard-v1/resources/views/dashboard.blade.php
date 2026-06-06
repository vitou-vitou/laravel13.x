<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Analytics Dashboard') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}
            </p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <livewire:dashboard-metrics />

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Revenue Trend') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Paid revenue over the last 7 days') }}</p>
                        <div class="mt-6 h-64">
                            <canvas id="revenue-trend-chart" aria-label="Revenue trend chart" role="img"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Order Status') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Orders grouped by status') }}</p>
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
        @vite(['resources/js/dashboard-charts.js'])
    @endpush
</x-app-layout>
