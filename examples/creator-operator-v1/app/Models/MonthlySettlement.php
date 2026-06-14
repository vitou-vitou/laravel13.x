<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use App\Enums\SettlementPlatform;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'creator_id',
    'period_start',
    'period_end',
    'platform',
    'gross_payout_local',
    'currency',
    'payout_status',
    's_views',
    't_views',
    'attributed_revenue',
    'commission_rate_pct',
    'monthly_ops_fee',
    'commission_amount',
    'creator_net',
    'notes',
])]
class MonthlySettlement extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'platform' => SettlementPlatform::class,
            'payout_status' => PayoutStatus::class,
            'gross_payout_local' => 'decimal:2',
            'attributed_revenue' => 'decimal:2',
            'commission_rate_pct' => 'decimal:2',
            'monthly_ops_fee' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'creator_net' => 'decimal:2',
            's_views' => 'integer',
            't_views' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }
}
