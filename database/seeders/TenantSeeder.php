<?php

namespace Database\Seeders;

use App\Enums\Currency;
use App\Enums\Role;
use App\Models\Policy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Seed the launch insurer tenant, one user per role, and a demo policy.
     */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['name' => 'Launch Insurer'],
            ['default_currency' => Currency::KHR->value, 'settings' => null],
        );

        $staff = [
            [Role::InsurerAdmin, 'admin@launch-insurer.test', 'Insurer Admin'],
            [Role::Adjuster, 'adjuster@launch-insurer.test', 'Claims Adjuster'],
            [Role::Finance, 'finance@launch-insurer.test', 'Finance Officer'],
        ];

        foreach ($staff as [$role, $email, $name]) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $name,
                    'locale' => 'en',
                    'role' => $role->value,
                    'password' => Hash::make('password'),
                ],
            );
        }

        $policyholder = User::firstOrCreate(
            ['email' => 'policyholder@launch-insurer.test'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Sok Dara',
                'locale' => 'km',
                'role' => Role::Policyholder->value,
                'password' => Hash::make('password'),
            ],
        );

        Policy::firstOrCreate(
            ['tenant_id' => $tenant->id, 'policy_number' => 'POL-00000001'],
            [
                'policyholder_user_id' => $policyholder->id,
                'vehicle_make' => 'Toyota',
                'vehicle_model' => 'Camry',
                'vehicle_year' => 2019,
                'vehicle_plate' => '2A-1234',
                'coverage_amount_minor' => 20_000_00,
                'currency' => Currency::KHR->value,
                'active_from' => now()->subMonth()->toDateString(),
                'active_to' => now()->addMonths(11)->toDateString(),
                'status' => 'active',
            ],
        );
    }
}
