<?php

namespace App\Services;

class DashboardMetricsService
{
    /**
     * @return list<array{label: string, value: string, trend: string, change: string}>
     */
    public function getKpis(): array
    {
        return [
            [
                'label' => 'Total Revenue',
                'value' => '$48,290',
                'trend' => 'up',
                'change' => '+12.4%',
            ],
            [
                'label' => 'Active Users',
                'value' => '2,847',
                'trend' => 'up',
                'change' => '+5.1%',
            ],
            [
                'label' => 'Orders Today',
                'value' => '186',
                'trend' => 'down',
                'change' => '-3.2%',
            ],
            [
                'label' => 'Conversion Rate',
                'value' => '3.8%',
                'trend' => 'neutral',
                'change' => '0.0%',
            ],
        ];
    }

    /**
     * @return list<array{customer: string, amount: string, status: string, date: string}>
     */
    public function getRecentOrders(): array
    {
        return [
            [
                'customer' => 'Jordan Lee',
                'amount' => '$129.00',
                'status' => 'Paid',
                'date' => 'Jun 6, 2026',
            ],
            [
                'customer' => 'Sam Patel',
                'amount' => '$84.50',
                'status' => 'Pending',
                'date' => 'Jun 6, 2026',
            ],
            [
                'customer' => 'Taylor Kim',
                'amount' => '$210.00',
                'status' => 'Paid',
                'date' => 'Jun 5, 2026',
            ],
            [
                'customer' => 'Morgan Chen',
                'amount' => '$56.25',
                'status' => 'Refunded',
                'date' => 'Jun 5, 2026',
            ],
        ];
    }
}
