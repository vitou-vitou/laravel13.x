<?php

namespace Tests\Feature;

use App\Enums\OrderGroupStatus;
use App\Enums\OrderStatus;
use App\Enums\PayoutStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use App\Services\PayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivered_order_group_schedules_payout(): void
    {
        $vendor = Vendor::factory()->create();
        $group = OrderGroup::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => OrderGroupStatus::Shipped,
            'subtotal_cents' => 10000,
            'commission_bps' => 1000,
        ]);

        $payout = app(PayoutService::class)->scheduleForDelivered($group->fresh());

        $this->assertSame(PayoutStatus::Completed, $payout->status);
        $this->assertSame(9000, $payout->amount_cents);
    }
}
