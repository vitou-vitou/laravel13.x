<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\TelegramBot;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Demo Company',
            'slug' => 'demo-company',
            'plan' => 'free',
        ]);

        User::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Admin',
            'email' => 'admin@demo.test',
            'password' => Hash::make('password'),
        ]);

        $application = Application::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo App',
            'redirect_uris' => ['http://localhost/callback'],
        ]);

        TelegramBot::query()->create([
            'application_id' => $application->id,
            'bot_username' => 'demo_bot',
            'bot_token' => '123456:TEST_TOKEN_FOR_DEMO_ONLY',
            'domains' => ['localhost'],
        ]);
    }
}
