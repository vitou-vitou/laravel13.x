<?php

namespace Tests\Unit;

use App\Enums\Currency;
use App\Enums\PolicyStatus;
use App\Models\Policy;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_returns_only_currently_active_policies(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        app(Tenancy::class)->set($tenant->id);

        $active = Policy::factory()->forTenant($tenant)->create(['policyholder_user_id' => $user->id]);
        Policy::factory()->forTenant($tenant)->expired()->create(['policyholder_user_id' => $user->id]);
        Policy::factory()->forTenant($tenant)->cancelled()->create(['policyholder_user_id' => $user->id]);

        $result = Policy::active()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($active));
    }

    public function test_active_policies_for_a_policyholder_query(): void
    {
        $tenant = Tenant::factory()->create();
        $owner = User::factory()->forTenant($tenant)->create();
        $other = User::factory()->forTenant($tenant)->create();
        app(Tenancy::class)->set($tenant->id);

        $mine = Policy::factory()->forTenant($tenant)->create(['policyholder_user_id' => $owner->id]);
        Policy::factory()->forTenant($tenant)->create(['policyholder_user_id' => $other->id]);

        $result = Policy::active()->forPolicyholder($owner)->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($mine));
    }

    public function test_casts_expose_enums_and_integer_amount(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();
        app(Tenancy::class)->set($tenant->id);

        $policy = Policy::factory()->forTenant($tenant)->create([
            'policyholder_user_id' => $user->id,
            'coverage_amount_minor' => 2_000_00,
            'currency' => 'USD',
        ]);

        $this->assertSame(Currency::USD, $policy->currency);
        $this->assertSame(PolicyStatus::Active, $policy->status);
        $this->assertIsInt($policy->coverage_amount_minor);
        $this->assertTrue($policy->isActive());
    }
}
