<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::query()->create([
            'name' => ['en' => 'Electronics', 'es' => 'Electrónica'],
            'slug' => 'electronics',
        ]);

        $apparel = Category::query()->create([
            'name' => ['en' => 'Apparel', 'es' => 'Ropa'],
            'slug' => 'apparel',
        ]);

        Product::query()->create([
            'category_id' => $electronics->id,
            'name' => ['en' => 'Wireless Headphones', 'es' => 'Auriculares inalámbricos'],
            'description' => [
                'en' => 'Noise-cancelling over-ear headphones.',
                'es' => 'Auriculares circumaurales con cancelación de ruido.',
            ],
            'slug' => 'wireless-headphones',
            'price_cents' => 12_999,
        ]);

        Product::query()->create([
            'category_id' => $apparel->id,
            'name' => ['en' => 'Studio Hoodie', 'es' => 'Sudadera Studio'],
            'description' => [
                'en' => 'Soft cotton blend hoodie.',
                'es' => 'Sudadera suave de algodón.',
            ],
            'slug' => 'studio-hoodie',
            'price_cents' => 4_500,
        ]);
    }
}
