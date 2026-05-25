<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Electronics', 'Clothing', 'Food & Beverage', 'Office Supplies', 'Tools'];
        $suppliers = ['SupplyCo', 'GlobalTrade', 'FastShip', 'LocalVendor', 'BestSource'];
        $statuses = ['active', 'active', 'active', 'inactive', 'discontinued'];

        $products = [
            ['MacBook Pro 14"', 'ELEC', 'Electronics', 12, 5, 1999.00, 1500.00],
            ['iPhone 15', 'ELEC', 'Electronics', 45, 10, 999.00, 750.00],
            ['USB-C Hub', 'ELEC', 'Electronics', 3, 15, 49.99, 20.00],
            ['Wireless Mouse', 'ELEC', 'Electronics', 60, 20, 29.99, 12.00],
            ['Mechanical Keyboard', 'ELEC', 'Electronics', 8, 10, 149.00, 80.00],
            ['Monitor 27"', 'ELEC', 'Electronics', 0, 5, 399.00, 250.00],
            ['T-Shirt (S)', 'CLTH', 'Clothing', 120, 30, 19.99, 5.00],
            ['T-Shirt (M)', 'CLTH', 'Clothing', 85, 30, 19.99, 5.00],
            ['T-Shirt (L)', 'CLTH', 'Clothing', 7, 30, 19.99, 5.00],
            ['Hoodie Black', 'CLTH', 'Clothing', 40, 15, 59.99, 20.00],
            ['Running Shoes', 'CLTH', 'Clothing', 22, 10, 89.99, 35.00],
            ['Denim Jeans', 'CLTH', 'Clothing', 0, 10, 69.99, 25.00],
            ['Coffee Beans 1kg', 'FOOD', 'Food & Beverage', 55, 20, 24.99, 10.00],
            ['Green Tea Box', 'FOOD', 'Food & Beverage', 90, 25, 12.99, 4.00],
            ['Protein Bar (x12)', 'FOOD', 'Food & Beverage', 4, 20, 34.99, 15.00],
            ['Sparkling Water', 'FOOD', 'Food & Beverage', 200, 50, 9.99, 3.00],
            ['A4 Paper (500)', 'OFFC', 'Office Supplies', 150, 40, 8.99, 3.00],
            ['Ballpoint Pens (x10)', 'OFFC', 'Office Supplies', 75, 20, 4.99, 1.50],
            ['Stapler', 'OFFC', 'Office Supplies', 6, 10, 14.99, 6.00],
            ['Whiteboard Markers', 'OFFC', 'Office Supplies', 30, 15, 9.99, 3.00],
            ['Hammer', 'TOOL', 'Tools', 18, 5, 24.99, 10.00],
            ['Screwdriver Set', 'TOOL', 'Tools', 12, 5, 39.99, 15.00],
            ['Drill 18V', 'TOOL', 'Tools', 2, 3, 129.00, 70.00],
            ['Measuring Tape', 'TOOL', 'Tools', 35, 10, 12.99, 4.00],
            ['Safety Gloves', 'TOOL', 'Tools', 60, 20, 7.99, 2.50],
        ];

        foreach ($products as $i => [$name, $skuPrefix, $category, $qty, $threshold, $price, $cost]) {
            $sku = $skuPrefix . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
            $status = $qty === 0 ? 'out_of_stock' : ($qty <= $threshold ? 'low_stock' : 'active');
            DB::table('products')->insert([
                'name' => $name,
                'sku' => $sku,
                'category' => $category,
                'quantity' => $qty,
                'low_stock_threshold' => $threshold,
                'price' => $price,
                'cost' => $cost,
                'supplier' => $suppliers[array_rand($suppliers)],
                'status' => $status,
                'created_at' => now()->subDays(rand(1, 180)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);
        }
    }
}
