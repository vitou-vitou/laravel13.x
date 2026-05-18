<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'number' => 'INV-' . $this->faker->unique()->numerify('######'),
            'issued_on' => now()->toDateString(),
            'due_on' => now()->addDays(30)->toDateString(),
            'status' => 'draft',
        ];
    }
}
