<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogBoard;
use App\Services\SupabaseActionLogger;
use App\Services\SupabaseHealthCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupabaseHealthCheckController extends Controller
{
    /**
     * @var list<string>
     */
    private const ALLOWED_TRIGGERS = [
        SupabaseActionLogger::TRIGGER_TAB_OPEN,
        SupabaseActionLogger::TRIGGER_TAB_RESTORE,
        SupabaseActionLogger::TRIGGER_TEST_CONNECTION,
    ];

    public function __invoke(
        Request $request,
        SupabaseHealthCheck $supabaseHealthCheck,
        ActivityLogBoard $activityLogBoard,
    ): JsonResponse {
        $trigger = $request->input('trigger', SupabaseActionLogger::TRIGGER_TEST_CONNECTION);

        if (! in_array($trigger, self::ALLOWED_TRIGGERS, true)) {
            $trigger = SupabaseActionLogger::TRIGGER_TEST_CONNECTION;
        }

        $health = $supabaseHealthCheck->check($trigger);
        $logs = $activityLogBoard->snapshotForApi();
        $latestLog = $logs['entries'][0] ?? null;

        return response()->json([
            'health' => $health,
            'logs' => $logs,
            'log' => $latestLog,
        ]);
    }
}
