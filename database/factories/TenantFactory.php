<?php

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Insurance',
            'default_currency' => Currency::KHR->value,
            'settings' => null,
        ];
    }
}
