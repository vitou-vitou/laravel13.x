<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_checkout_notification_payload(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->withSession([
                'checkout_order_id' => 42,
                'checkout_customer' => 'Jane Doe',
                'checkout_total' => '$25.00',
            ])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="checkout-order-notification"', false)
            ->assertSee('"customer":"Jane Doe"', false)
            ->assertSee('"amount":"$25.00"', false);
    }
}
