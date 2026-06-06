<?php

use App\Services\DashboardMetricsService;
use Livewire\Component;

new class extends Component
{
    public function with(DashboardMetricsService $metrics): array
    {
        return [
            'kpis' => $metrics->getKpis(),
            'recentOrders' => $metrics->getRecentOrders(),
        ];
    }
};
?>

<div class="space-y-8">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">{{ __('Desktop alerts when a new order arrives (requires browser permission).') }}</p>
        <button type="button"
                id="enable-order-notifications"
                data-enabled-label="{{ __('Notifications enabled') }}"
                class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50">
            {{ __('Enable order notifications') }}
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($kpis as $kpi)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <p class="text-sm font-medium text-gray-500">{{ $kpi['label'] }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $kpi['value'] }}</p>
                    <p @class([
                        'mt-2 text-sm font-medium',
                        'text-emerald-600' => $kpi['trend'] === 'up',
                        'text-rose-600' => $kpi['trend'] === 'down',
                        'text-gray-500' => $kpi['trend'] === 'neutral',
                    ])>
                        {{ $kpi['change'] }} vs last period
                    </p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Recent Orders') }}</h3>
                <p class="text-xs text-gray-400">{{ __('Updates in real time via Echo') }}</p>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($recentOrders as $order)
                            <tr wire:key="order-{{ $order['id'] }}">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $order['customer'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $order['amount'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $order['status'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $order['date'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No orders yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
