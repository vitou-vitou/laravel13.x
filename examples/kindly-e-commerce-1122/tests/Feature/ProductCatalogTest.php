<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    public function test_shop_lists_active_products(): void
    {
        $response = $this->get(route('shop.index'));

        $response->assertOk();
        $response->assertSee('Kindly Mug', false);
        $response->assertSee('Kindly Tote', false);
        $response->assertSee('Kindly Notebook', false);
    }

    public function test_inactive_products_are_hidden(): void
    {
        Product::query()->where('slug', 'kindly-mug')->update(['is_active' => false]);

        $response = $this->get(route('shop.index'));

        $response->assertOk();
        $response->assertDontSee('Kindly Mug', false);
        $response->assertSee('Kindly Tote', false);
    }
}
