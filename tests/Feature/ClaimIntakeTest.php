<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Policy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimIntakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_policyholder_can_save_update_and_submit_a_claim_draft(): void
    {
        [$tenant, $policyholder] = $this->policyholder();
        $policy = Policy::factory()->forTenant($tenant)->create([
            'policyholder_user_id' => $policyholder->id,
        ]);

        $draft = $this->actingAs($policyholder, 'api')
            ->postJson('/api/v1/claims', ['policy_id' => $policy->id])
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft');

        $claimId = $draft->json('data.id');

        $this->patchJson("/api/v1/claims/{$claimId}", [
            'incident_at' => now()->subHour()->toIso8601String(),
            'location' => 'Phnom Penh',
            'incident_type' => 'collision',
            'description' => 'Rear bumper damage after a collision.',
        ])->assertOk()->assertJsonPath('data.status', 'draft');

        $this->postJson("/api/v1/claims/{$claimId}/submit")
            ->assertOk()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.policy_id', $policy->id)
            ->assertJsonPath('data.reference', fn (string $reference): bool => str_starts_with($reference, 'CLM-'));

        $this->assertDatabaseHas('claims', [
            'id' => $claimId,
            'tenant_id' => $tenant->id,
            'policy_id' => $policy->id,
            'status' => 'submitted',
        ]);
    }

    public function test_policyholder_cannot_submit_a_claim_for_another_users_policy(): void
    {
        [$tenant, $policyholder] = $this->policyholder();
        $other = User::factory()->forTenant($tenant)->policyholder()->create();
        $foreignPolicy = Policy::factory()->forTenant($tenant)->create([
            'policyholder_user_id' => $other->id,
        ]);

        $this->actingAs($policyholder, 'api')
            ->postJson('/api/v1/claims', $this->claimData($foreignPolicy->id))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('policy_id');

        $this->assertDatabaseCount('claims', 0);
    }

    public function test_policyholder_cannot_submit_a_claim_for_an_expired_policy(): void
    {
        [$tenant, $policyholder] = $this->policyholder();
        $policy = Policy::factory()->forTenant($tenant)->expired()->create([
            'policyholder_user_id' => $policyholder->id,
        ]);

        $this->actingAs($policyholder, 'api')
            ->postJson('/api/v1/claims', $this->claimData($policy->id))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('policy_id');

        $this->assertDatabaseCount('claims', 0);
    }

    public function test_only_the_draft_owner_can_update_or_submit_it(): void
    {
        [$tenant, $owner] = $this->policyholder();
        $other = User::factory()->forTenant($tenant)->policyholder()->create();
        $claim = Claim::factory()->forTenant($tenant)->create([
            'policyholder_user_id' => $owner->id,
        ]);

        $this->actingAs($other, 'api')
            ->patchJson("/api/v1/claims/{$claim->id}", ['location' => 'Kampot'])
            ->assertForbidden();

        $this->postJson("/api/v1/claims/{$claim->id}/submit")
            ->assertForbidden();
    }

    /**
     * @return array{0: Tenant, 1: User}
     */
    private function policyholder(): array
    {
        $tenant = Tenant::factory()->create();
        $policyholder = User::factory()->forTenant($tenant)->policyholder()->create();

        return [$tenant, $policyholder];
    }

    /**
     * @return array<string, mixed>
     */
    private function claimData(int $policyId): array
    {
        return [
            'policy_id' => $policyId,
            'incident_at' => now()->subHour()->toIso8601String(),
            'location' => 'Phnom Penh',
            'incident_type' => 'collision',
            'description' => 'Rear bumper damage after a collision.',
        ];
    }
}
