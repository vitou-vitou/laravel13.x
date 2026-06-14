<?php

namespace Tests\Feature;

use App\Enums\VendorStatus;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_pending_vendor(): void
    {
        $admin = User::factory()->admin()->create();
        $vendor = Vendor::factory()->pending()->create();

        $this->actingAs($admin)
            ->post(route('admin.vendors.approve', $vendor))
            ->assertRedirect();

        $this->assertSame(VendorStatus::Active, $vendor->fresh()->status);
    }
}
