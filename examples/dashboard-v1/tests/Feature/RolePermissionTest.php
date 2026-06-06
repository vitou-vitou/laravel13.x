<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_has_access_admin_permission(): void
    {
        $admin = $this->adminUser();

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->can('access_admin'));
    }

    public function test_customer_role_cannot_access_admin_panel(): void
    {
        $this->seedRoles();

        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->assertFalse($user->can('access_admin'));
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }
}
