<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Kindly Mug',
                'slug' => 'kindly-mug',
                'description' => 'Ceramic mug with Kindly logo.',
                'price_cents' => 1299,
                'stock_quantity' => 25,
            ],
            [
                'name' => 'Kindly Tote',
                'slug' => 'kindly-tote',
                'description' => 'Canvas tote for everyday carry.',
                'price_cents' => 2499,
                'stock_quantity' => 15,
            ],
            [
                'name' => 'Kindly Notebook',
                'slug' => 'kindly-notebook',
                'description' => 'Dot-grid notebook, 120 pages.',
                'price_cents' => 899,
                'stock_quantity' => 40,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['slug' => $product['slug']],
                array_merge($product, ['is_active' => true]),
            );
        }
    }
}
