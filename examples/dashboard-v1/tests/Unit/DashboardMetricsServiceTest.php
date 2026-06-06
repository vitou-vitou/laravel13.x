<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_revenue_sums_paid_orders_only(): void
    {
        Order::factory()->paid()->create(['amount_cents' => 10_000]);
        Order::factory()->paid()->create(['amount_cents' => 5_000]);
        Order::factory()->pending()->create(['amount_cents' => 99_999]);

        $kpis = (new DashboardMetricsService)->getKpis();

        $revenue = collect($kpis)->firstWhere('label', 'Total Revenue');

        $this->assertSame('$150.00', $revenue['value']);
    }

    public function test_orders_today_counts_orders_on_current_date(): void
    {
        Order::factory()->create(['ordered_at' => now()]);
        Order::factory()->create(['ordered_at' => now()]);
        Order::factory()->create(['ordered_at' => now()->subDay()]);

        $kpis = (new DashboardMetricsService)->getKpis();

        $ordersToday = collect($kpis)->firstWhere('label', 'Orders Today');

        $this->assertSame('2', $ordersToday['value']);
    }

    public function test_active_users_reflects_user_count(): void
    {
        User::factory()->count(3)->create();

        $kpis = (new DashboardMetricsService)->getKpis();

        $activeUsers = collect($kpis)->firstWhere('label', 'Active Users');

        $this->assertSame('3', $activeUsers['value']);
    }

    public function test_conversion_rate_is_paid_over_total_orders(): void
    {
        Order::factory()->paid()->count(3)->create();
        Order::factory()->pending()->count(1)->create();

        $kpis = (new DashboardMetricsService)->getKpis();

        $conversion = collect($kpis)->firstWhere('label', 'Conversion Rate');

        $this->assertSame('75.0%', $conversion['value']);
    }

    public function test_recent_orders_returns_latest_ten_by_date(): void
    {
        $recentCustomer = Customer::factory()->create(['name' => 'Acme Corp']);
        $olderCustomer = Customer::factory()->create(['name' => 'Older Customer']);

        Order::factory()->forCustomer($olderCustomer)->create([
            'ordered_at' => now()->subDays(5),
        ]);
        Order::factory()->forCustomer($recentCustomer)->create([
            'ordered_at' => now(),
        ]);

        $recent = (new DashboardMetricsService)->getRecentOrders();

        $this->assertSame('Acme Corp', $recent[0]['customer']);
        $this->assertSame('Older Customer', $recent[1]['customer']);
    }

    public function test_revenue_trend_returns_seven_days_of_paid_revenue(): void
    {
        Order::factory()->paid()->create([
            'amount_cents' => 5_000,
            'ordered_at' => today(),
        ]);
        Order::factory()->paid()->create([
            'amount_cents' => 2_500,
            'ordered_at' => today()->subDay(),
        ]);

        $trend = (new DashboardMetricsService)->getRevenueTrend();

        $this->assertCount(7, $trend['labels']);
        $this->assertCount(7, $trend['values']);
        $this->assertSame(50.0, $trend['values'][6]);
        $this->assertSame(25.0, $trend['values'][5]);
    }

    public function test_status_breakdown_counts_orders_by_status(): void
    {
        Order::factory()->paid()->count(2)->create();
        Order::factory()->pending()->count(1)->create();
        Order::factory()->refunded()->count(1)->create();

        $breakdown = (new DashboardMetricsService)->getStatusBreakdown();

        $this->assertSame(['Paid', 'Pending', 'Refunded'], $breakdown['labels']);
        $this->assertSame([2, 1, 1], $breakdown['values']);
    }

    public function test_get_latest_order_id_returns_zero_when_empty(): void
    {
        $this->assertSame(0, (new DashboardMetricsService)->getLatestOrderId());
    }

    public function test_get_orders_newer_than_returns_only_new_orders(): void
    {
        $first = Order::factory()->create();
        $second = Order::factory()->create();

        $orders = (new DashboardMetricsService)->getOrdersNewerThan($first->id);

        $this->assertCount(1, $orders);
        $this->assertSame($second->id, $orders[0]->id);
    }
}
