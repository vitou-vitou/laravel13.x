<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\ActivityLogBoard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityLogsController extends Controller
{
    public function __invoke(Request $request, ActivityLogBoard $activityLogBoard): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:'.ActivityLogBoard::MAX_LIMIT],
            'offset' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(ActivityLog::STATUSES)],
            'search' => ['nullable', 'string', 'max:255'],
            'grouped' => ['nullable', 'boolean'],
        ]);

        $offset = max((int) ($validated['offset'] ?? 0), 0);
        $status = $validated['status'] ?? null;
        $search = $validated['search'] ?? null;

        // Grouped: per-status pagination for the board (top N per status).
        if ($request->boolean('grouped')) {
            $perStatus = min(max((int) ($validated['limit'] ?? ActivityLogBoard::DEFAULT_LIMIT), 1), ActivityLogBoard::MAX_LIMIT);

            return response()->json($activityLogBoard->groupedSnapshotForApi($perStatus, $status, $search));
        }

        // Flat: a single status slice used by "Load more" within one group.
        $limit = min(max((int) ($validated['limit'] ?? ActivityLogBoard::MAX_LIMIT), 1), ActivityLogBoard::MAX_LIMIT);

        return response()->json($activityLogBoard->snapshotForApi($limit, $offset, $status, $search));
    }
}
