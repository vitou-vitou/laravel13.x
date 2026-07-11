<?php

namespace Tests\Feature;

use App\Enums\OrderGroupStatus;
use App\Models\OrderGroup;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_user_can_view_main_dashboard(): void
    {
        $vendor = Vendor::factory()->create();

        $this->actingAs($vendor->user)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Vendor dashboard', false);
    }

    public function test_vendor_can_confirm_pending_order_group(): void
    {
        $vendor = Vendor::factory()->create();
        $group = OrderGroup::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => OrderGroupStatus::Pending,
        ]);

        $this->actingAs($vendor->user)
            ->post(route('vendor.orders.confirm', $group))
            ->assertRedirect(route('vendor.orders.show', $group))
            ->assertSessionHas('status');

        $this->assertSame(OrderGroupStatus::Confirmed, $group->fresh()->status);
    }

    public function test_get_on_confirm_url_redirects_to_dashboard(): void
    {
        $vendor = Vendor::factory()->create();
        $group = OrderGroup::factory()->create(['vendor_id' => $vendor->id]);

        $this->actingAs($vendor->user)
            ->get('/vendor/orders/'.$group->id.'/confirm')
            ->assertRedirect(route('vendor.dashboard'));
    }
}
