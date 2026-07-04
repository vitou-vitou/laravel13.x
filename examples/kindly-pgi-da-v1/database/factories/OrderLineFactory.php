<?php

namespace Database\Factories;

use App\Models\OrderGroup;
use App\Models\OrderLine;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderLine> */
class OrderLineFactory extends Factory
{
    protected $model = OrderLine::class;

    public function definition(): array
    {
        return [
            'order_group_id' => OrderGroup::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => 1,
            'unit_price_cents' => 1000,
            'product_name_snapshot' => 'Test product',
            'variant_name_snapshot' => 'Default',
        ];
    }
}
