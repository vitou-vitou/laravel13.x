<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $order = Order::query()->create([
            'user_id' => $owner->id,
            'status' => 'pending',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
        ]);

        $this->actingAs($other)
            ->get(route('orders.show', $order))
            ->assertForbidden();
    }

    public function test_admin_can_view_another_users_order(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $order = Order::query()->create([
            'user_id' => $owner->id,
            'status' => 'pending',
            'subtotal_cents' => 1000,
            'discount_cents' => 0,
            'total_cents' => 1000,
        ]);

        $this->actingAs($admin)
            ->get(route('orders.show', $order))
            ->assertOk();
    }
}
