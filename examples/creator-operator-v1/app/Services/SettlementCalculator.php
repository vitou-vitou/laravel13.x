<?php

namespace App\Services;

class SettlementCalculator
{
    /**
     * @return array{attributed_revenue: float, commission_amount: float, creator_net: float}
     */
    public function calculate(
        float $grossPayoutLocal,
        int $sViews,
        int $tViews,
        float $commissionRatePct,
        float $monthlyOpsFee,
    ): array {
        $attributed = $tViews > 0
            ? round($grossPayoutLocal * ($sViews / $tViews), 2)
            : 0.0;

        $commission = round($attributed * ($commissionRatePct / 100), 2);
        $creatorNet = round($attributed - $monthlyOpsFee - $commission, 2);

        return [
            'attributed_revenue' => $attributed,
            'commission_amount' => $commission,
            'creator_net' => $creatorNet,
        ];
    }
}
