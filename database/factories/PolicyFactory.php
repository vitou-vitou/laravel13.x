<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Enums\PolicyStatus;
use App\Models\Policy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Policy>
 */
class PolicyFactory extends Factory
{
    protected $model = Policy::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'policyholder_user_id' => User::factory(),
            'policy_number' => 'POL-'.fake()->unique()->numerify('########'),
            'vehicle_make' => fake()->randomElement(['Toyota', 'Honda', 'Lexus', 'Hyundai', 'Ford']),
            'vehicle_model' => fake()->randomElement(['Camry', 'CR-V', 'RX', 'Accent', 'Ranger']),
            'vehicle_year' => fake()->numberBetween(2005, 2024),
            'vehicle_plate' => strtoupper(fake()->bothify('##?-####')),
            'coverage_amount_minor' => fake()->numberBetween(1_000, 50_000) * 100,
            'currency' => Currency::KHR->value,
            'active_from' => now()->subMonths(1)->toDateString(),
            'active_to' => now()->addMonths(11)->toDateString(),
            'status' => PolicyStatus::Active->value,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'active_from' => now()->subYear()->toDateString(),
            'active_to' => now()->subMonth()->toDateString(),
            'status' => PolicyStatus::Expired->value,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PolicyStatus::Cancelled->value,
        ]);
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
        ]);
    }
}
