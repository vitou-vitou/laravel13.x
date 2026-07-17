<?php

namespace Tests\Feature\Livewire;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardMetricsComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_kpis_without_poll_directive(): void
    {
        User::factory()->create();

        Livewire::test('dashboard-metrics')
            ->assertSee('Total Revenue')
            ->assertSee('Recent Orders')
            ->assertSee('Updates in real time via Echo')
            ->assertDontSeeHtml('wire:poll');
    }

    public function test_component_reflects_new_orders_after_refresh(): void
    {
        User::factory()->create();

        Order::factory()->paid()->create(['amount_cents' => 10_000]);

        $component = Livewire::test('dashboard-metrics')
            ->assertSee('$100.00');

        Order::factory()->paid()->create(['amount_cents' => 5_000]);

        $component->call('$refresh')
            ->assertSee('$150.00');
    }
}
