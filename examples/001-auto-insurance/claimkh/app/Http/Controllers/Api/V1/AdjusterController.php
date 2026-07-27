<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdjusterController extends Controller
{
    public function queue(Request $request): JsonResponse
    {
        $user = $request->user();

        $claims = Claim::where('tenant_id', $user->tenant_id)
            ->where('adjuster_id', $user->id)
            ->whereIn('status', ['submitted', 'under_review', 'info_requested'])
            ->orderBy('estimated_resolution_date', 'asc')
            ->with(['claimant', 'documents'])
            ->get()
            ->map(fn($claim) => $this->formatQueueItem($claim));

        return response()->json(['claims' => $claims]);
    }

    public function show(Request $request, string $ref): JsonResponse
    {
        $claim = Claim::where('ref', $ref)
            ->where('tenant_id', $request->user()->tenant_id)
            ->with(['claimant', 'adjuster', 'documents', 'auditLogs.actor'])
            ->firstOrFail();

        return response()->json(['claim' => $this->formatDetailedClaim($claim)]);
    }

    private function formatQueueItem(Claim $claim): array
    {
        $isOverdue = $claim->estimated_resolution_date &&
            $claim->estimated_resolution_date->isPast();

        return [
            'ref' => $claim->ref,
            'type' => $claim->type,
            'status' => $claim->status,
            'incident_at' => $claim->incident_at->toISOString(),
            'estimated_resolution_date' => $claim->estimated_resolution_date?->toDateString(),
            'is_overdue' => $isOverdue,
            'claimant_name' => $claim->claimant->name,
            'document_count' => $claim->documents->count(),
            'missing_docs' => $claim->missing_docs ?? [],
        ];
    }

    private function formatDetailedClaim(Claim $claim): array
    {
        return [
            'ref' => $claim->ref,
            'type' => $claim->type,
            'status' => $claim->status,
            'incident_at' => $claim->incident_at->toISOString(),
            'location' => [
                'lat' => $claim->lat,
                'lng' => $claim->lng,
                'address' => $claim->address,
            ],
            'description' => $claim->description,
            'estimated_resolution_date' => $claim->estimated_resolution_date?->toDateString(),
            'missing_docs' => $claim->missing_docs ?? [],
            'claimant' => [
                'id' => $claim->claimant->id,
                'phone' => $claim->claimant->phone,
                'name' => $claim->claimant->name,
            ],
            'documents' => $claim->documents->map(fn($d) => [
                'id' => $d->id,
                'type' => $d->type,
                'filename' => $d->original_filename,
                'status' => $d->status,
            ]),
            'audit_logs' => $claim->auditLogs->map(fn($log) => [
                'action' => $log->action,
                'actor' => $log->actor->name ?? $log->actor->phone,
                'old_status' => $log->old_status,
                'new_status' => $log->new_status,
                'note' => $log->note,
                'created_at' => $log->created_at->toISOString(),
            ]),
        ];
    }
}
