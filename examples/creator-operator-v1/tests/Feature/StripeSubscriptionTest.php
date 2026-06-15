<?php

namespace Tests\Feature;

use App\Enums\OperatorPlan;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class StripeSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_settings_redirects_when_stripe_not_configured(): void
    {
        config([
            'operator-billing.mode' => 'mock',
            'cashier.secret' => null,
        ]);

        $operator = User::factory()->create([
            'role' => UserRole::Operator,
            'operator_plan' => OperatorPlan::Starter,
        ]);

        $this->actingAs($operator)
            ->get(route('settings.subscription'))
            ->assertRedirect(route('operator.billing.index'));
    }

    public function test_creator_cannot_access_subscription_settings(): void
    {
        $creator = User::factory()->create([
            'role' => UserRole::Creator,
        ]);

        $this->actingAs($creator)
            ->get(route('settings.subscription'))
            ->assertForbidden();
    }

    public function test_active_stripe_subscription_grants_pro_creator_limit(): void
    {
        config([
            'operator-billing.mode' => 'stripe',
            'cashier.secret' => 'sk_test_fake',
            'operator-billing.stripe_prices.pro' => 'price_pro_test',
        ]);

        $operator = User::factory()->create([
            'role' => UserRole::Operator,
            'operator_plan' => OperatorPlan::Starter,
            'stripe_id' => 'cus_test',
        ]);

        Subscription::create([
            'user_id' => $operator->id,
            'type' => 'default',
            'stripe_id' => 'sub_test',
            'stripe_status' => 'active',
            'stripe_price' => 'price_pro_test',
            'quantity' => 1,
        ]);

        $this->assertSame(OperatorPlan::Pro->creatorLimit(), $operator->fresh()->creatorLimit());
    }

    public function test_mock_plan_update_blocked_when_stripe_mode_active(): void
    {
        config([
            'operator-billing.mode' => 'stripe',
            'cashier.secret' => 'sk_test_fake',
            'operator-billing.stripe_prices.pro' => 'price_pro_test',
        ]);

        $operator = User::factory()->create([
            'role' => UserRole::Operator,
            'operator_plan' => OperatorPlan::Starter,
        ]);

        $this->actingAs($operator)
            ->post(route('operator.billing.plan'), [
                'operator_plan' => OperatorPlan::Pro->value,
            ])
            ->assertRedirect(route('settings.subscription'));
    }
}
