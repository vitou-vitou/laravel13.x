<?php

namespace Tests\Feature;

use App\Enums\PayoutStatus;
use App\Enums\SettlementPlatform;
use App\Models\Creator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlySettlementTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_settlement_uses_s_over_t_formula(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create();

        $response = $this->actingAs($operator)->post(route('operator.creators.settlement.store', $creator), [
            'period_start' => '2026-06-01',
            'period_end' => '2026-06-30',
            'platform' => SettlementPlatform::Youtube->value,
            'gross_payout_local' => 1000,
            'currency' => 'USD',
            'payout_status' => PayoutStatus::Estimated->value,
            's_views' => 50000,
            't_views' => 100000,
            'commission_rate_pct' => 15,
            'monthly_ops_fee' => 50,
            'notes' => 'June pilot',
        ]);

        $response->assertRedirect(route('operator.creators.settlement.index', $creator));

        $this->assertDatabaseHas('monthly_settlements', [
            'creator_id' => $creator->id,
            'attributed_revenue' => 500.00,
            'commission_amount' => 75.00,
            'creator_net' => 375.00,
        ]);
    }

    public function test_creator_sees_settlement_statement(): void
    {
        $creatorUser = User::factory()->creator()->create();
        $creator = Creator::factory()->create(['user_id' => $creatorUser->id]);

        $creator->monthlySettlements()->create([
            'period_start' => '2026-06-01',
            'period_end' => '2026-06-30',
            'platform' => SettlementPlatform::Youtube,
            'gross_payout_local' => 100,
            'currency' => 'USD',
            'payout_status' => PayoutStatus::Confirmed,
            's_views' => 1000,
            't_views' => 2000,
            'attributed_revenue' => 50,
            'commission_rate_pct' => 10,
            'monthly_ops_fee' => 5,
            'commission_amount' => 5,
            'creator_net' => 40,
        ]);

        $this->actingAs($creatorUser)
            ->get(route('creator.settlement.index'))
            ->assertOk()
            ->assertSee('40.00');
    }
}
