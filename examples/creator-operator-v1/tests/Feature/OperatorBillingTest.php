<?php

namespace Tests\Feature;

use App\Enums\OperatorPlan;
use App\Models\Creator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_starter_plan_blocks_fourth_creator(): void
    {
        $operator = User::factory()->operator()->create([
            'operator_plan' => OperatorPlan::Starter,
        ]);

        Creator::factory()->count(3)->create();

        $response = $this->actingAs($operator)->post(route('operator.creators.store'), [
            'handle' => 'fourthcreator',
            'tiktok_url' => 'https://www.tiktok.com/@fourthcreator',
            'tier' => 'lite',
            'music_policy' => 'skip',
        ]);

        $response->assertSessionHasErrors('handle');
        $this->assertDatabaseMissing('creators', ['handle' => 'fourthcreator']);
    }

    public function test_operator_can_switch_to_pro_plan(): void
    {
        $operator = User::factory()->operator()->create([
            'operator_plan' => OperatorPlan::Starter,
        ]);

        $this->actingAs($operator)
            ->post(route('operator.billing.plan'), [
                'operator_plan' => OperatorPlan::Pro->value,
            ])
            ->assertRedirect();

        $this->assertSame(OperatorPlan::Pro, $operator->fresh()->operator_plan);
    }
}
