<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_filters_by_max_price(): void
    {
        [$cheap, $expensive] = $this->twoPricedProducts(1500, 7500);

        $this->get(route('catalog.index', ['max_price' => 20]))
            ->assertOk()
            ->assertSee($cheap->name)
            ->assertDontSee($expensive->name);
    }

    public function test_catalog_filters_by_min_price(): void
    {
        [$cheap, $expensive] = $this->twoPricedProducts(1500, 7500);

        $this->get(route('catalog.index', ['min_price' => 50]))
            ->assertOk()
            ->assertSee($expensive->name)
            ->assertDontSee($cheap->name);
    }

    public function test_catalog_sorts_by_price_ascending(): void
    {
        [$cheap, $expensive] = $this->twoPricedProducts(1500, 7500);

        $response = $this->get(route('catalog.index', ['sort' => 'price_asc']))->assertOk();

        $cheapPos = strpos($response->getContent(), $cheap->name);
        $expensivePos = strpos($response->getContent(), $expensive->name);

        $this->assertNotFalse($cheapPos);
        $this->assertNotFalse($expensivePos);
        $this->assertLessThan($expensivePos, $cheapPos);
    }

    public function test_home_shows_featured_categories(): void
    {
        $category = Category::factory()->create(['name' => 'Handmade Goods']);
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'status' => ProductStatus::Active,
        ]);
        ProductVariant::factory()->create(['product_id' => $product->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Shop by category')
            ->assertSee('Handmade Goods');
    }

    public function test_recently_viewed_appears_on_home_after_pdp_visit(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Woven Basket',
            'status' => ProductStatus::Active,
        ]);
        ProductVariant::factory()->create(['product_id' => $product->id]);

        $this->get(route('catalog.show', $product))->assertOk();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Recently viewed')
            ->assertSee('Woven Basket');
    }

    public function test_sticky_cart_bar_shows_after_adding_item(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'status' => ProductStatus::Active]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price_cents' => 2500]);

        $this->actingAs($user)
            ->post(route('cart.store'), [
                'product_variant_id' => $variant->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('cart.index'));

        $this->actingAs($user)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('View cart')
            ->assertSee('2 items');
    }

    public function test_filter_query_string_preserved_in_pagination_links(): void
    {
        $vendor = Vendor::factory()->create();

        for ($i = 0; $i < 13; $i++) {
            $product = Product::factory()->create([
                'vendor_id' => $vendor->id,
                'status' => ProductStatus::Active,
            ]);
            ProductVariant::factory()->create(['product_id' => $product->id, 'price_cents' => 1000 + $i]);
        }

        $this->get(route('catalog.index', ['sort' => 'price_asc', 'min_price' => 5]))
            ->assertOk()
            ->assertSee('sort=price_asc', false)
            ->assertSee('min_price=5', false);
    }

    /**
     * @return array{0: Product, 1: Product}
     */
    private function twoPricedProducts(int $cheapCents, int $expensiveCents): array
    {
        $vendor = Vendor::factory()->create();

        $cheap = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Budget Lamp',
            'status' => ProductStatus::Active,
        ]);
        ProductVariant::factory()->create(['product_id' => $cheap->id, 'price_cents' => $cheapCents]);

        $expensive = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'name' => 'Premium Lamp',
            'status' => ProductStatus::Active,
        ]);
        ProductVariant::factory()->create(['product_id' => $expensive->id, 'price_cents' => $expensiveCents]);

        return [$cheap, $expensive];
    }
}
