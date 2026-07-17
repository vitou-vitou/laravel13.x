<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount_cents' => fake()->numberBetween(1_000, 50_000),
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
            'method' => fake()->randomElement(['card', 'manual']),
            'reference' => fake()->optional()->uuid(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
