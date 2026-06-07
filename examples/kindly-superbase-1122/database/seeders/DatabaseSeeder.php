<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\SupabaseActionLogger;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            User::factory()->make(['name' => 'Test User', 'email' => 'test@example.com'])->toArray(),
        );

        if (ActivityLog::query()->exists()) {
            return;
        }

        $logs = [
            ['sync_profiles', ActivityLog::STATUS_PROCESSING, 'Syncing user profiles from Supabase'],
            ['webhook_delivery', ActivityLog::STATUS_FAILED, 'Webhook delivery failed for order #1042'],
            [SupabaseActionLogger::ACTION_HEALTH_CHECK, ActivityLog::STATUS_COMPLETED, 'Supabase health check: Supabase auth service is reachable.'],
            ['storage_migration', ActivityLog::STATUS_PENDING, 'Queued storage bucket migration'],
            ['token_cache', ActivityLog::STATUS_PROCESSING, 'Refreshing auth token cache'],
            ['policy_validation', ActivityLog::STATUS_FAILED, 'Row-level policy validation error on posts'],
            ['edge_deploy', ActivityLog::STATUS_COMPLETED, 'Edge function deploy completed'],
        ];

        foreach ($logs as [$action, $status, $message]) {
            $transactionId = SupabaseActionLogger::generateTransactionId();

            ActivityLog::query()->create([
                'transaction_id' => $transactionId,
                'action' => $action,
                'status' => $status,
                'message' => $message,
                'context' => ['source' => 'supabase', 'transaction_id' => $transactionId],
            ]);
        }
    }
}
