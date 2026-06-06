<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'customer' => ['name' => 'Jordan Lee', 'email' => 'jordan@example.com'],
                'order' => ['amount_cents' => 12_900, 'status' => 'paid', 'ordered_at' => now()],
            ],
            [
                'customer' => ['name' => 'Sam Patel', 'email' => 'sam@example.com'],
                'order' => ['amount_cents' => 8_450, 'status' => 'pending', 'ordered_at' => now()],
            ],
            [
                'customer' => ['name' => 'Taylor Kim', 'email' => 'taylor@example.com'],
                'order' => ['amount_cents' => 21_000, 'status' => 'paid', 'ordered_at' => now()->subDay()],
            ],
            [
                'customer' => ['name' => 'Morgan Chen', 'email' => 'morgan@example.com'],
                'order' => ['amount_cents' => 5_625, 'status' => 'refunded', 'ordered_at' => now()->subDay()],
            ],
        ];

        foreach ($rows as $row) {
            $customer = Customer::query()->create($row['customer']);

            Order::query()->create([
                ...$row['order'],
                'customer_id' => $customer->id,
            ]);
        }
    }
}
