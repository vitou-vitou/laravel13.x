<?php

namespace Database\Factories;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Claim>
 */
class ClaimFactory extends Factory
{
    protected $model = Claim::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'policy_id' => null,
            'policyholder_user_id' => User::factory(),
            'incident_type' => 'collision',
            'incident_at' => now()->subHour(),
            'location' => fake()->city(),
            'description' => fake()->sentence(),
            'status' => ClaimStatus::Draft->value,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes) => ['tenant_id' => $tenant->id]);
    }
}
