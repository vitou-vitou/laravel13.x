<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_active_products(): void
    {
        $category = Category::factory()->create();
        $vendor = Vendor::factory()->create();

        $active = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'status' => ProductStatus::Active,
            'name' => 'Visible Mug',
        ]);

        ProductVariant::factory()->create(['product_id' => $active->id]);

        Product::factory()->draft()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Hidden Draft',
        ]);

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Visible Mug')
            ->assertDontSee('Hidden Draft');
    }
}
