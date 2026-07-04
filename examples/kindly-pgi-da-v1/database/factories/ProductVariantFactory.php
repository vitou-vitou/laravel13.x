<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductVariant> */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'name' => 'Default',
            'price_cents' => fake()->numberBetween(500, 50000),
            'stock_qty' => fake()->numberBetween(1, 100),
            'status' => 'active',
        ];
    }
}
