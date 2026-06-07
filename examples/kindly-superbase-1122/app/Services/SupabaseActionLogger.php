<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Str;

class SupabaseActionLogger
{
    public const ACTION_HEALTH_CHECK = 'health_check';

    public const TRIGGER_TAB_OPEN = 'tab_open';

    public const TRIGGER_TAB_RESTORE = 'tab_restore';

    public const TRIGGER_TEST_CONNECTION = 'test_connection';

    public const TRANSACTION_ID_PREFIX = 'sup_';

    public static function generateTransactionId(): string
    {
        return self::TRANSACTION_ID_PREFIX.Str::uuid();
    }

    /**
     * @param  array{
     *     status: string,
     *     message: string,
     *     endpoint?: ?string,
     *     checked_at?: string,
     *     http_status?: int,
     *     details?: array<string, mixed>
     * }  $result
     */
    public function logHealthCheck(array $result, string $trigger): ActivityLog
    {
        $context = [
            'source' => 'supabase',
            'result' => $result['status'],
            'trigger' => $trigger,
            'checked_at' => $result['checked_at'] ?? now()->toIso8601String(),
        ];

        if (! empty($result['details'])) {
            $context['details'] = $result['details'];
        }

        if (isset($result['http_status'])) {
            $context['http_status'] = $result['http_status'];
        }

        return $this->record(
            action: self::ACTION_HEALTH_CHECK,
            status: $this->mapHealthResultToLogStatus($result['status']),
            message: 'Supabase health check: '.$result['message'],
            context: $context,
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $action, string $status, string $message, array $context = []): ActivityLog
    {
        $transactionId = self::generateTransactionId();

        $context['transaction_id'] = $transactionId;

        return ActivityLog::query()->create([
            'transaction_id' => $transactionId,
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public function mapHealthResultToLogStatus(string $healthStatus): string
    {
        return match ($healthStatus) {
            'healthy' => ActivityLog::STATUS_COMPLETED,
            'unhealthy' => ActivityLog::STATUS_FAILED,
            default => ActivityLog::STATUS_PENDING,
        };
    }
}
