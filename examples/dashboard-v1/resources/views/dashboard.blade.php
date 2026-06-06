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
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Recent Orders') }}</h3>
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
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $order['customer'] }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $order['amount'] }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $order['status'] }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $order['date'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
