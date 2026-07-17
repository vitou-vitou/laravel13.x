<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'access_admin',
            'manage_catalog',
            'manage_orders',
            'manage_customers',
            'manage_payments',
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'manage_dev_tunnels',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $admin = Role::findOrCreate('admin');
        $admin->syncPermissions(Permission::all());

        $staff = Role::findOrCreate('staff');
        $staff->syncPermissions([
            'access_admin',
            'manage_catalog',
            'manage_orders',
            'manage_customers',
            'manage_payments',
        ]);

        Role::findOrCreate('customer');
    }
}
