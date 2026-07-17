<?php

namespace Database\Factories;

use App\Models\Tunnel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tunnel>
 */
class TunnelFactory extends Factory
{
    protected $model = Tunnel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->unique()->slug(2);

        return [
            'name' => fake()->unique()->words(2, true),
            'domain' => "{$slug}.ngrok-free.dev",
            'herd_host' => config('tunnel.default_herd_host', 'dashboard-v1.test'),
            'is_active' => false,
            'last_verified_at' => null,
            'last_verified_status' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
