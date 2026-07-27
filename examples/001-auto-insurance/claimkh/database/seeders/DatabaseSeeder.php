<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::create([
            'name' => 'AIA Cambodia',
            'subdomain' => 'aia',
            'config' => [
                'sla' => ['collision' => 7, 'theft' => 14, 'injury' => 10],
                'required_docs' => [
                    'collision' => ['driver_license', 'vehicle_registration', 'police_report', 'photo'],
                    'theft' => ['driver_license', 'vehicle_registration', 'police_report'],
                    'injury' => ['driver_license', 'vehicle_registration', 'police_report', 'photo'],
                ],
            ],
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'phone' => '+85510000001',
            'name' => 'Super Admin',
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'phone' => '+85510000002',
            'name' => 'Sok Adjuster',
            'role' => 'adjuster',
            'is_active' => true,
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'phone' => '+85512345678',
            'name' => 'Chan Claimant',
            'role' => 'claimant',
            'is_active' => true,
        ]);
    }
}
