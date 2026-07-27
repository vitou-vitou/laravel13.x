<?php

namespace Tests\Feature;

use App\Models\Policy;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_scope_isolates_reads_to_current_tenant(): void
    {
        [$tenantA, $userA] = $this->tenantWithUser();
        [$tenantB, $userB] = $this->tenantWithUser();

        Policy::factory()->forTenant($tenantA)->create(['policyholder_user_id' => $userA->id]);
        Policy::factory()->forTenant($tenantB)->create(['policyholder_user_id' => $userB->id]);

        app(Tenancy::class)->set($tenantA->id);

        $this->assertSame(1, Policy::count());
        $this->assertTrue(Policy::all()->every(fn (Policy $p) => $p->tenant_id === $tenantA->id));
    }

    public function test_cross_tenant_record_is_not_readable(): void
    {
        [$tenantA, $userA] = $this->tenantWithUser();
        [$tenantB, $userB] = $this->tenantWithUser();

        $foreign = Policy::factory()->forTenant($tenantB)->create(['policyholder_user_id' => $userB->id]);

        app(Tenancy::class)->set($tenantA->id);

        $this->assertNull(Policy::find($foreign->id));
    }

    public function test_new_record_is_stamped_with_current_tenant(): void
    {
        [$tenantA, $userA] = $this->tenantWithUser();

        app(Tenancy::class)->set($tenantA->id);

        $policy = Policy::create([
            'policyholder_user_id' => $userA->id,
            'policy_number' => 'POL-AUTO-1',
            'coverage_amount_minor' => 1_000_00,
            'currency' => 'KHR',
            'active_from' => now()->toDateString(),
            'active_to' => now()->addYear()->toDateString(),
            'status' => 'active',
        ]);

        $this->assertSame($tenantA->id, $policy->tenant_id);
    }

    public function test_tenant_context_falls_back_to_authenticated_user(): void
    {
        [$tenantA, $userA] = $this->tenantWithUser();
        [$tenantB, $userB] = $this->tenantWithUser();

        Policy::factory()->forTenant($tenantA)->create(['policyholder_user_id' => $userA->id]);
        Policy::factory()->forTenant($tenantB)->create(['policyholder_user_id' => $userB->id]);

        $this->actingAs($userA);

        $this->assertSame(1, Policy::count());
    }

    /**
     * @return array{0: Tenant, 1: User}
     */
    private function tenantWithUser(): array
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create();

        return [$tenant, $user];
    }
}
