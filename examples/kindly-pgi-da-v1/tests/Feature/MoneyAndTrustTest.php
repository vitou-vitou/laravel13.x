<?php

namespace Tests\Feature;

use App\Enums\DisputeStatus;
use App\Enums\OrderGroupStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PayoutStatus;
use App\Enums\ProductStatus;
use App\Enums\PromoCodeType;
use App\Enums\RefundStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CartService;
use App\Services\PayoutService;
use App\Services\PromoCodeService;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MoneyAndTrustTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_issue_partial_refund_with_audit_trail(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->paidOrder(amountCents: 10000);

        $this->actingAs($admin)
            ->post(route('admin.orders.refund', $order), [
                'amount_cents' => 2500,
                'reason' => 'Damaged item',
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('status');

        $payment = $order->payment->fresh();
        $this->assertSame(2500, $payment->refunded_cents);
        $this->assertSame(PaymentStatus::Completed, $payment->status);

        $this->assertDatabaseHas('refunds', [
            'order_id' => $order->id,
            'amount_cents' => 2500,
            'status' => RefundStatus::Completed->value,
            'reason' => 'Damaged item',
        ]);

        $this->assertDatabaseHas('payment_audit_logs', [
            'payment_id' => $payment->id,
            'note' => 'Admin refund #1: Damaged item',
        ]);
    }

    public function test_full_refund_marks_order_refunded(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->paidOrder(amountCents: 5000);

        app(RefundService::class)->issue($order, 5000, 'Full refund', $admin);

        $this->assertSame(OrderStatus::Refunded, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Refunded, $order->payment->fresh()->status);
    }

    public function test_vendor_connect_onboarding_sets_stripe_account(): void
    {
        $vendorUser = User::factory()->create(['role' => UserRole::Vendor]);
        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'stripe_account_id' => null,
        ]);

        $this->actingAs($vendorUser)
            ->get(route('vendor.connect.start'))
            ->assertRedirect(route('vendor.connect.callback'));

        $this->assertNotNull($vendor->fresh()->stripe_account_id);
    }

    public function test_payout_records_stripe_transfer_when_vendor_connected(): void
    {
        $vendor = Vendor::factory()->create([
            'stripe_account_id' => 'acct_test_123',
        ]);

        $group = OrderGroup::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => OrderGroupStatus::Delivered,
            'subtotal_cents' => 10000,
            'commission_bps' => 1000,
        ]);

        $payout = app(PayoutService::class)->scheduleForDelivered($group->fresh());

        $this->assertSame(PayoutStatus::Completed, $payout->status);
        $this->assertSame('tr_fake_'.$payout->id, $payout->stripe_transfer_id);
        $this->assertNotNull($payout->released_at);
    }

    public function test_open_dispute_freezes_payout_release(): void
    {
        $vendor = Vendor::factory()->create([
            'stripe_account_id' => 'acct_test_456',
        ]);

        $group = OrderGroup::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => OrderGroupStatus::Delivered,
            'subtotal_cents' => 8000,
            'commission_bps' => 1000,
        ]);

        Dispute::query()->create([
            'order_group_id' => $group->id,
            'user_id' => User::factory()->create()->id,
            'status' => DisputeStatus::Opened,
            'reason' => 'Item not received',
        ]);

        $payout = app(PayoutService::class)->scheduleForDelivered($group->fresh());

        $this->assertSame(PayoutStatus::Pending, $payout->status);
        $this->assertNull($payout->stripe_transfer_id);
    }

    public function test_vendor_scoped_promo_discounts_only_matching_lines(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $category = Category::factory()->create();
        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();

        $variantA = ProductVariant::factory()->create([
            'product_id' => Product::factory()->create([
                'vendor_id' => $vendorA->id,
                'category_id' => $category->id,
                'status' => ProductStatus::Active,
            ])->id,
            'price_cents' => 6000,
            'stock_qty' => 5,
        ]);

        $variantB = ProductVariant::factory()->create([
            'product_id' => Product::factory()->create([
                'vendor_id' => $vendorB->id,
                'category_id' => $category->id,
                'status' => ProductStatus::Active,
            ])->id,
            'price_cents' => 4000,
            'stock_qty' => 5,
        ]);

        PromoCode::factory()->create([
            'code' => 'VENDOR10',
            'vendor_id' => $vendorA->id,
            'type' => PromoCodeType::Percent,
            'value' => 10,
        ]);

        $this->actingAs($customer);
        $cart = app(CartService::class);
        $cart->add($variantA, 1);
        $cart->add($variantB, 1);

        $lines = $cart->lines();
        $promo = app(PromoCodeService::class);

        $discount = $promo->discountCents($promo->findByCode('VENDOR10'), $cart->totalCents(), $lines);

        $this->assertSame(600, $discount);
    }

    public function test_promo_rejects_when_min_subtotal_not_met(): void
    {
        $this->customerWithCartItem(3000);

        PromoCode::factory()->create([
            'code' => 'BIGORDER',
            'min_subtotal_cents' => 5000,
            'type' => PromoCodeType::Fixed,
            'value' => 500,
        ]);

        $this->expectException(ValidationException::class);

        app(PromoCodeService::class)->applyToSession(
            'BIGORDER',
            app(CartService::class)->totalCents(),
            app(CartService::class)->lines(),
        );
    }

    public function test_cart_shows_buyer_protection_copy(): void
    {
        $customer = $this->customerWithCartItem(2000);

        $this->actingAs($customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Buyer protection');
    }

    public function test_paid_order_page_shows_buyer_protection_copy(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $order = $this->paidOrder(amountCents: 2000, userId: $customer->id);

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Buyer protection');
    }

    private function paidOrder(int $amountCents, ?int $userId = null): Order
    {
        $userId ??= User::factory()->create()->id;

        $order = Order::factory()->create([
            'user_id' => $userId,
            'status' => OrderStatus::Paid,
            'total_cents' => $amountCents,
            'subtotal_cents' => $amountCents,
            'paid_at' => now(),
        ]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => PaymentStatus::Completed,
            'amount_cents' => $amountCents,
            'refunded_cents' => 0,
        ]);

        return $order->fresh(['payment']);
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
