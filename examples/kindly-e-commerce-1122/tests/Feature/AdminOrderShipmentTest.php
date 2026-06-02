<?php

namespace Tests\Feature;

use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminOrderShipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_user_cannot_mark_order_shipped(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $owner = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $owner->id,
            'status' => 'paid',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
            'paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('admin.orders.ship', $order))
            ->assertForbidden();
    }

    public function test_admin_can_mark_paid_order_as_shipped_and_queue_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $owner->id,
            'status' => 'paid',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.ship', $order))
            ->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame('shipped', $order->status);
        $this->assertNotNull($order->shipped_at);
        Mail::assertQueued(OrderShippedMail::class, 1);
    }

    public function test_admin_cannot_mark_pending_order_as_shipped(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $owner->id,
            'status' => 'pending',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.ship', $order))
            ->assertRedirect(route('orders.show', $order));

        $this->assertSame('pending', $order->fresh()->status);
        Mail::assertNothingQueued();
    }
}
