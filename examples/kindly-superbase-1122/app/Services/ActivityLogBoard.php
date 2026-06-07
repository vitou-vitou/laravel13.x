<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Collection;

class ActivityLogBoard
{
    public const DEFAULT_LIMIT = 5;

    public const SHOW_MORE_LIMIT = 10;

    public const MAX_LIMIT = 200;

    public const MESSAGE_PREVIEW_LENGTH = 120;

    /**
     * @return array{
     *     summary: array<string, int>,
     *     entries: Collection<int, ActivityLog>,
     *     showing: int,
     *     total: int,
     *     has_more: bool,
     *     offset: int,
     *     limit: int,
     *     status: string|null,
     *     search: string|null
     * }
     */
    public function snapshot(int $limit = self::DEFAULT_LIMIT, int $offset = 0, ?string $status = null, ?string $search = null): array
    {
        $search = $this->normalizeSearch($search);

        $summary = $this->summaryCounts();

        $entriesQuery = ActivityLog::query()->latest('id');
        $totalQuery = ActivityLog::query();

        if ($status !== null) {
            $entriesQuery->where('status', $status);
            $totalQuery->where('status', $status);
        }

        if ($search !== null) {
            $entriesQuery->where('transaction_id', 'like', '%'.$search.'%');
            $totalQuery->where('transaction_id', 'like', '%'.$search.'%');
        }

        $total = (int) $totalQuery->count();
        $entries = $entriesQuery
            ->offset($offset)
            ->limit($limit)
            ->get();

        return [
            'summary' => $summary,
            'entries' => $entries,
            'showing' => $offset === 0 ? $entries->count() : $offset + $entries->count(),
            'total' => $total,
            'has_more' => $offset + $entries->count() < $total,
            'offset' => $offset,
            'limit' => $limit,
            'status' => $status,
            'search' => $search,
        ];
    }

    private function normalizeSearch(?string $search): ?string
    {
        if ($search === null) {
            return null;
        }

        $search = trim($search);

        return $search === '' ? null : $search;
    }

    /**
     * @return array<string, int>
     */
    private function summaryCounts(): array
    {
        $counts = ActivityLog::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [];
        foreach (ActivityLog::STATUSES as $statusKey) {
            $summary[$statusKey] = (int) ($counts[$statusKey] ?? 0);
        }

        return $summary;
    }

    /**
     * Per-status snapshot: each status shows its latest $perStatus entries plus
     * its own total/has_more so the board can paginate one group at a time.
     *
     * @return array{
     *     summary: array<string, int>,
     *     groups: list<array{status: string, entries: Collection<int, ActivityLog>, total: int, has_more: bool}>,
     *     showing: int,
     *     total: int,
     *     per_status: int,
     *     status: string|null,
     *     search: string|null
     * }
     */
    public function groupedSnapshot(int $perStatus = self::DEFAULT_LIMIT, ?string $status = null, ?string $search = null): array
    {
        $search = $this->normalizeSearch($search);
        $perStatus = min(max($perStatus, 1), self::MAX_LIMIT);

        $statuses = $status !== null ? [$status] : ActivityLog::STATUSES;

        $groups = [];
        $showing = 0;
        $total = 0;

        foreach ($statuses as $statusKey) {
            $query = ActivityLog::query()->where('status', $statusKey);

            if ($search !== null) {
                $query->where('transaction_id', 'like', '%'.$search.'%');
            }

            $groupTotal = (int) (clone $query)->count();
            $entries = (clone $query)->latest('id')->limit($perStatus)->get();

            $groups[] = [
                'status' => $statusKey,
                'entries' => $entries,
                'total' => $groupTotal,
                'has_more' => $groupTotal > $entries->count(),
            ];

            $showing += $entries->count();
            $total += $groupTotal;
        }

        return [
            'summary' => $this->summaryCounts(),
            'groups' => $groups,
            'showing' => $showing,
            'total' => $total,
            'per_status' => $perStatus,
            'status' => $status,
            'search' => $search,
        ];
    }

    /**
     * @return array{
     *     summary: array<string, int>,
     *     grouped: true,
     *     groups: list<array{status: string, entries: list<array<string, mixed>>, total: int, has_more: bool}>,
     *     showing: int,
     *     total: int,
     *     per_status: int,
     *     status: string|null,
     *     search: string|null
     * }
     */
    public function groupedSnapshotForApi(int $perStatus = self::DEFAULT_LIMIT, ?string $status = null, ?string $search = null): array
    {
        $snapshot = $this->groupedSnapshot($perStatus, $status, $search);

        return [
            'summary' => $snapshot['summary'],
            'grouped' => true,
            'groups' => array_map(fn (array $group) => [
                'status' => $group['status'],
                'entries' => $group['entries']
                    ->map(fn (ActivityLog $entry) => $this->formatEntry($entry))
                    ->values()
                    ->all(),
                'total' => $group['total'],
                'has_more' => $group['has_more'],
            ], $snapshot['groups']),
            'showing' => $snapshot['showing'],
            'total' => $snapshot['total'],
            'per_status' => $snapshot['per_status'],
            'status' => $snapshot['status'],
            'search' => $snapshot['search'],
        ];
    }

    /**
     * @return array{
     *     summary: array<string, int>,
     *     entries: list<array<string, mixed>>,
     *     showing: int,
     *     total: int,
     *     has_more: bool,
     *     offset: int,
     *     limit: int,
     *     status: string|null,
     *     search: string|null
     * }
     */
    public function snapshotForApi(int $limit = self::DEFAULT_LIMIT, int $offset = 0, ?string $status = null, ?string $search = null): array
    {
        $snapshot = $this->snapshot($limit, $offset, $status, $search);

        return [
            'summary' => $snapshot['summary'],
            'entries' => $snapshot['entries']
                ->map(fn (ActivityLog $entry) => $this->formatEntry($entry))
                ->values()
                ->all(),
            'showing' => $snapshot['showing'],
            'total' => $snapshot['total'],
            'has_more' => $snapshot['has_more'],
            'offset' => $snapshot['offset'],
            'limit' => $snapshot['limit'],
            'status' => $snapshot['status'],
            'search' => $snapshot['search'],
        ];
    }

    public function shouldTruncateMessage(string $message): bool
    {
        return mb_strlen($message) > self::MESSAGE_PREVIEW_LENGTH;
    }

    public function previewMessage(string $message): string
    {
        return mb_strimwidth($message, 0, self::MESSAGE_PREVIEW_LENGTH, '…');
    }

    /**
     * @return array<string, mixed>
     */
    public function formatEntry(ActivityLog $entry): array
    {
        $trigger = $entry->context['trigger'] ?? null;
        $message = $entry->message;
        $truncate = $this->shouldTruncateMessage($message);

        return [
            'id' => $entry->id,
            'transaction_id' => $entry->transaction_id,
            'status' => $entry->status,
            'message' => $message,
            'message_preview' => $truncate ? $this->previewMessage($message) : $message,
            'message_truncated' => $truncate,
            'action' => $entry->action,
            'action_label' => $entry->action ? str_replace('_', ' ', $entry->action) : null,
            'trigger' => $trigger,
            'trigger_label' => is_string($trigger) ? str_replace('_', ' ', $trigger) : null,
            'created_at' => $entry->created_at?->toIso8601String(),
            'created_at_human' => $entry->created_at?->diffForHumans(),
        ];
    }
}
