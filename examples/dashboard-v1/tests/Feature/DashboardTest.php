<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_kpi_cards(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Analytics Dashboard');
        $response->assertSee('Total Revenue');
        $response->assertSee('Active Users');
        $response->assertSee('Orders Today');
        $response->assertSee('Conversion Rate');
    }

    public function test_dashboard_shows_recent_orders_from_database(): void
    {
        $user = User::factory()->create();

        Order::factory()->create([
            'customer_name' => 'Acme Corp',
            'ordered_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Recent Orders');
        $response->assertSee('Customer');
        $response->assertSee('Amount');
        $response->assertSee('Status');
        $response->assertSee('Date');
        $response->assertSee('Acme Corp');
    }

    public function test_dashboard_shows_revenue_from_paid_orders(): void
    {
        $user = User::factory()->create();

        Order::factory()->paid()->create(['amount_cents' => 25_000]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('$250.00');
    }

    public function test_dashboard_welcomes_user_by_name(): void
    {
        $user = User::factory()->create(['name' => 'Alex Rivera']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Welcome back, Alex Rivera');
    }

    public function test_dashboard_includes_chart_sections_and_data(): void
    {
        $user = User::factory()->create();
        Order::factory()->paid()->create(['amount_cents' => 10_000, 'ordered_at' => now()]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Revenue Trend', false);
        $response->assertSee('Order Status', false);
        $response->assertSee('id="revenue-trend-chart"', false);
        $response->assertSee('id="order-status-chart"', false);
        $response->assertSee('id="dashboard-charts-data"', false);
        $response->assertSee('"labels"', false);
        $response->assertSeeHtml('wire:poll.30s');
    }
}
