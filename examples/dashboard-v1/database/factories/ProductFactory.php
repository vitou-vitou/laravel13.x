<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => [
                'en' => ucfirst($name),
                'es' => ucfirst($name).' ES',
            ],
            'description' => [
                'en' => fake()->sentence(),
                'es' => fake()->sentence().' (ES)',
            ],
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'price_cents' => fake()->numberBetween(500, 50_000),
            'is_active' => true,
        ];
    }
}
