<?php

namespace Database\Factories;

use App\Enums\PayoutStatus;
use App\Enums\SettlementPlatform;
use App\Models\Creator;
use App\Models\MonthlySettlement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonthlySettlement>
 */
class MonthlySettlementFactory extends Factory
{
    protected $model = MonthlySettlement::class;

    public function definition(): array
    {
        return [
            'creator_id' => Creator::factory(),
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'platform' => SettlementPlatform::Youtube,
            'gross_payout_local' => 100.00,
            'currency' => 'USD',
            'payout_status' => PayoutStatus::Estimated,
            's_views' => 50000,
            't_views' => 120000,
            'attributed_revenue' => 41.67,
            'commission_rate_pct' => 15,
            'monthly_ops_fee' => 0,
            'commission_amount' => 6.25,
            'creator_net' => 35.42,
            'notes' => null,
        ];
    }
}
