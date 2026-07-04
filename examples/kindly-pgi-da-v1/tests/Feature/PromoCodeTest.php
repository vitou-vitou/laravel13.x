<?php

namespace Tests\Feature;

use App\Enums\PromoCodeType;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\PromoCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_apply_valid_percent_promo_on_cart(): void
    {
        $customer = $this->customerWithCartItem(10000);

        PromoCode::factory()->create([
            'code' => 'SAVE10',
            'type' => PromoCodeType::Percent,
            'value' => 10,
        ]);

        $this->actingAs($customer)
            ->post(route('cart.promo.apply'), ['code' => 'save10'])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('status');

        $this->assertSame('SAVE10', app(PromoCodeService::class)->appliedPromo()?->code);
    }

    public function test_invalid_promo_code_is_rejected(): void
    {
        $customer = $this->customerWithCartItem(5000);

        $this->actingAs($customer)
            ->post(route('cart.promo.apply'), ['code' => 'NOPE'])
            ->assertSessionHasErrors('code');
    }

    public function test_checkout_applies_discount_to_order_and_payment(): void
    {
        $customer = $this->customerWithCartItem(10000);

        $promo = PromoCode::factory()->create([
            'code' => 'FLAT20',
            'type' => PromoCodeType::Fixed,
            'value' => 2000,
        ]);

        app(PromoCodeService::class)->applyToSession('FLAT20', 10000, app(CartService::class)->lines());

        $order = app(CheckoutService::class)->placeFromCart();

        $this->assertSame(10000, $order->subtotal_cents);
        $this->assertSame(2000, $order->discount_cents);
        $this->assertSame(8000, $order->total_cents);
        $this->assertSame($promo->id, $order->promo_code_id);
        $this->assertSame(8000, $order->payment->amount_cents);
        $this->assertNull(app(PromoCodeService::class)->appliedPromo());
    }

    public function test_promo_use_count_increments_when_order_is_paid(): void
    {
        $customer = $this->customerWithCartItem(5000);

        $promo = PromoCode::factory()->create([
            'code' => 'ONCE',
            'type' => PromoCodeType::Fixed,
            'value' => 500,
            'max_uses' => 1,
            'uses_count' => 0,
        ]);

        app(PromoCodeService::class)->applyToSession('ONCE', 5000, app(CartService::class)->lines());
        $order = app(CheckoutService::class)->placeFromCart();

        app(PaymentService::class)->markPaid($order);

        $this->assertSame(1, $promo->fresh()->uses_count);
    }

    public function test_admin_can_create_promo_code(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.promo-codes.store'), [
                'code' => 'WELCOME15',
                'type' => 'percent',
                'value' => 15,
            ])
            ->assertRedirect(route('admin.promo-codes.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('promo_codes', [
            'code' => 'WELCOME15',
            'value' => 15,
        ]);
    }

    private function customerWithCartItem(int $priceCents): User
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $category = Category::factory()->create();
        $vendor = Vendor::factory()->create();

        $variant = ProductVariant::factory()->create([
            'product_id' => Product::factory()->create([
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'status' => ProductStatus::Active,
            ])->id,
            'price_cents' => $priceCents,
            'stock_qty' => 5,
        ]);

        $this->actingAs($customer);
        app(CartService::class)->add($variant, 1);

        return $customer;
    }
}
