<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardMetricsService
{
    /**
     * @return list<array{label: string, value: string, trend: string, change: string}>
     */
    public function getKpis(): array
    {
        return [
            $this->buildKpi(
                'Total Revenue',
                fn () => $this->formatMoney($this->paidRevenueCents()),
                fn (Carbon $start, Carbon $end) => $this->paidRevenueCentsBetween($start, $end),
            ),
            $this->buildKpi(
                'Active Users',
                fn () => number_format(User::query()->count()),
                fn (Carbon $start, Carbon $end) => User::query()
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ),
            $this->buildKpi(
                'Orders Today',
                fn () => (string) Order::query()->whereDate('ordered_at', today())->count(),
                fn (Carbon $start, Carbon $end) => Order::query()
                    ->whereBetween('ordered_at', [$start, $end])
                    ->count(),
            ),
            $this->buildConversionKpi(),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function getRevenueTrend(int $days = 7): array
    {
        $labels = [];
        $values = [];

        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = today()->subDays($offset);
            $labels[] = $date->format('M j');

            $cents = (int) Order::query()
                ->where('status', 'paid')
                ->whereDate('ordered_at', $date)
                ->sum('amount_cents');

            $values[] = round($cents / 100, 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    public function getStatusBreakdown(): array
    {
        $statuses = ['paid', 'pending', 'refunded'];

        return [
            'labels' => ['Paid', 'Pending', 'Refunded'],
            'values' => array_map(
                fn (string $status) => Order::query()->where('status', $status)->count(),
                $statuses,
            ),
        ];
    }

    /**
     * @return list<array{customer: string, amount: string, status: string, date: string}>
     */
    public function getRecentOrders(): array
    {
        return Order::query()
            ->orderByDesc('ordered_at')
            ->limit(10)
            ->get()
            ->map(fn (Order $order) => [
                'customer' => $order->customer_name,
                'amount' => $order->formattedAmount(),
                'status' => $order->statusLabel(),
                'date' => $order->ordered_at->format('M j, Y'),
            ])
            ->all();
    }

    /**
     * @param  callable(): (int|float|string)  $currentValue
     * @param  callable(Carbon, Carbon): (int|float)  $periodValue
     * @return array{label: string, value: string, trend: string, change: string}
     */
    private function buildKpi(string $label, callable $currentValue, callable $periodValue): array
    {
        [$currentPeriodStart, $currentPeriodEnd, $previousPeriodStart, $previousPeriodEnd] = $this->comparisonWindows();

        $current = (float) $periodValue($currentPeriodStart, $currentPeriodEnd);
        $previous = (float) $periodValue($previousPeriodStart, $previousPeriodEnd);

        if ($label === 'Total Revenue') {
            $displayValue = $this->formatMoney((int) $this->paidRevenueCents());
        } elseif ($label === 'Orders Today') {
            $displayValue = (string) Order::query()->whereDate('ordered_at', today())->count();
        } else {
            $displayValue = (string) $currentValue();
        }

        return [
            'label' => $label,
            'value' => $displayValue,
            'trend' => $this->trendFor($current, $previous),
            'change' => $this->changeLabelFor($current, $previous),
        ];
    }

    /**
     * @return array{label: string, value: string, trend: string, change: string}
     */
    private function buildConversionKpi(): array
    {
        $total = Order::query()->count();
        $paid = Order::query()->where('status', 'paid')->count();
        $rate = $total > 0 ? ($paid / $total) * 100 : 0.0;

        [$currentStart, $currentEnd, $previousStart, $previousEnd] = $this->comparisonWindows();

        $currentRate = $this->conversionRateBetween($currentStart, $currentEnd);
        $previousRate = $this->conversionRateBetween($previousStart, $previousEnd);

        return [
            'label' => 'Conversion Rate',
            'value' => number_format($rate, 1).'%',
            'trend' => $this->trendFor($currentRate, $previousRate),
            'change' => $this->changeLabelFor($currentRate, $previousRate),
        ];
    }

    private function paidRevenueCents(): int
    {
        return (int) Order::query()
            ->where('status', 'paid')
            ->sum('amount_cents');
    }

    private function paidRevenueCentsBetween(Carbon $start, Carbon $end): int
    {
        return (int) Order::query()
            ->where('status', 'paid')
            ->whereBetween('ordered_at', [$start, $end])
            ->sum('amount_cents');
    }

    private function conversionRateBetween(Carbon $start, Carbon $end): float
    {
        $total = Order::query()->whereBetween('ordered_at', [$start, $end])->count();

        if ($total === 0) {
            return 0.0;
        }

        $paid = Order::query()
            ->where('status', 'paid')
            ->whereBetween('ordered_at', [$start, $end])
            ->count();

        return ($paid / $total) * 100;
    }

    private function formatMoney(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: Carbon, 3: Carbon}
     */
    private function comparisonWindows(): array
    {
        $currentPeriodEnd = now();
        $currentPeriodStart = now()->subDays(7)->startOfDay();
        $previousPeriodEnd = $currentPeriodStart->copy()->subSecond();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays(7)->startOfDay();

        return [$currentPeriodStart, $currentPeriodEnd, $previousPeriodStart, $previousPeriodEnd];
    }

    private function trendFor(float $current, float $previous): string
    {
        if ($current > $previous) {
            return 'up';
        }

        if ($current < $previous) {
            return 'down';
        }

        return 'neutral';
    }

    private function changeLabelFor(float $current, float $previous): string
    {
        if ($previous == 0.0) {
            return $current == 0.0 ? '0.0%' : '+100.0%';
        }

        $change = (($current - $previous) / $previous) * 100;

        return sprintf('%+.1f%%', $change);
    }
}
