<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Enums\VendorStatus;
use App\Models\Category;
use App\Models\PlatformSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        PlatformSetting::query()->create(['default_commission_bps' => 1000]);

        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@marketplace.local',
        ]);

        $category = Category::factory()->create([
            'name' => 'General',
            'slug' => 'general',
        ]);

        foreach (['Kindly Crafts', 'Bright Goods'] as $storeName) {
            $vendorUser = User::factory()->vendor()->create([
                'name' => $storeName.' Owner',
                'email' => Str::slug($storeName).'@marketplace.local',
            ]);

            $vendor = Vendor::factory()->create([
                'user_id' => $vendorUser->id,
                'store_name' => $storeName,
                'slug' => Str::slug($storeName),
                'status' => VendorStatus::Active,
            ]);

            Product::factory()
                ->count(5)
                ->create([
                    'vendor_id' => $vendor->id,
                    'category_id' => $category->id,
                    'status' => ProductStatus::Active,
                ])
                ->each(function (Product $product): void {
                    ProductVariant::factory()->create([
                        'product_id' => $product->id,
                        'stock_qty' => 25,
                    ]);
                });
        }

        User::factory()->customer()->create([
            'name' => 'Customer',
            'email' => 'customer@marketplace.local',
        ]);
    }
}
