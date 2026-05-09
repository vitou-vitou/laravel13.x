<?php

namespace Database\Factories;

use App\Models\DemoItem;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemoItem>
 */
class DemoItemFactory extends Factory
{
    protected $model = DemoItem::class;

    public function definition(): array
    {
        return [
            'guest_id' => Guest::factory(),
            'title' => fake()->sentence(3),
            'body' => fake()->optional()->paragraph(),
        ];
    }
}
