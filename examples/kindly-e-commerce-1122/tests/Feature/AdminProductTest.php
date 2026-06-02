<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    public function test_guest_cannot_access_admin_products(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_product(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'name' => 'Kindly Sticker',
                'description' => 'Vinyl sticker pack',
                'price_cents' => 399,
                'stock_quantity' => 100,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'Kindly Sticker')->first();
        $this->assertNotNull($product);

        $this->actingAs($admin)
            ->put(route('admin.products.update', $product), [
                'name' => 'Kindly Sticker Pack',
                'description' => 'Updated',
                'price_cents' => 499,
                'stock_quantity' => 80,
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertSame('Kindly Sticker Pack', $product->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
