<?php

namespace Database\Factories;

use App\Enums\OrderGroupStatus;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderGroup> */
class OrderGroupFactory extends Factory
{
    protected $model = OrderGroup::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'vendor_id' => Vendor::factory(),
            'status' => OrderGroupStatus::Pending,
            'commission_bps' => 1000,
            'subtotal_cents' => fake()->numberBetween(500, 20000),
        ];
    }
}
