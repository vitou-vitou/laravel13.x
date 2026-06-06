<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            ['customer_name' => 'Jordan Lee', 'amount_cents' => 12_900, 'status' => 'paid', 'ordered_at' => now()],
            ['customer_name' => 'Sam Patel', 'amount_cents' => 8_450, 'status' => 'pending', 'ordered_at' => now()],
            ['customer_name' => 'Taylor Kim', 'amount_cents' => 21_000, 'status' => 'paid', 'ordered_at' => now()->subDay()],
            ['customer_name' => 'Morgan Chen', 'amount_cents' => 5_625, 'status' => 'refunded', 'ordered_at' => now()->subDay()],
        ];

        foreach ($orders as $order) {
            Order::query()->create($order);
        }
    }
}
