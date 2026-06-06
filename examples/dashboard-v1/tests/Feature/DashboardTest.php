<?php

namespace Tests\Feature;

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

    public function test_dashboard_shows_recent_orders_table(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Recent Orders');
        $response->assertSee('Customer');
        $response->assertSee('Amount');
        $response->assertSee('Status');
        $response->assertSee('Date');
        $response->assertSee('Jordan Lee');
    }

    public function test_dashboard_welcomes_user_by_name(): void
    {
        $user = User::factory()->create(['name' => 'Alex Rivera']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Welcome back, Alex Rivera');
    }
}
