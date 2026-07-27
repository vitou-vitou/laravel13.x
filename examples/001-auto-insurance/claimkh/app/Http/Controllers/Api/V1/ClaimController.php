<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\ClaimAuditLog;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClaimController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:collision,theft,injury'],
            'incident_at' => ['required', 'date', 'before_or_equal:now'],
            'location.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'location.address' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $user = $request->user();
        $tenant = $user->tenant;

        $lat = $request->input('location.lat');
        $lng = $request->input('location.lng');

        if ($lat === 0.0 && $lng === 0.0) {
            $lat = null;
            $lng = null;
        }

        // EC-4: duplicate detection
        $duplicate = Claim::where('tenant_id', $tenant->id)
            ->where('claimant_id', $user->id)
            ->where('incident_at', '>=', now()->subHours(24))
            ->whereDate('incident_at', now()->parse($request->incident_at)->toDateString())
            ->first();

        if ($duplicate) {
            return response()->json([
                'error' => 'duplicate_claim',
                'existing_ref' => $duplicate->ref,
            ], 409);
        }

        $ref = $this->generateRef();
        $sla = $tenant->slaForType($request->type);
        $estimatedDate = now()->addDays($sla)->toDateString();

        $requiredDocs = $tenant->requiredDocsForType($request->type);

        $claim = Claim::create([
            'tenant_id' => $tenant->id,
            'ref' => $ref,
            'claimant_id' => $user->id,
            'type' => $request->type,
            'status' => 'submitted',
            'incident_at' => $request->incident_at,
            'lat' => $lat,
            'lng' => $lng,
            'address' => $request->input('location.address'),
            'description' => $request->description,
            'estimated_resolution_date' => $estimatedDate,
            'missing_docs' => $requiredDocs,
        ]);

        ClaimAuditLog::create([
            'claim_id' => $claim->id,
            'actor_id' => $user->id,
            'action' => 'claim_submitted',
            'old_status' => null,
            'new_status' => 'submitted',
            'created_at' => now(),
        ]);

        return response()->json(['claim' => $this->formatClaim($claim)], 201);
    }

    public function show(Request $request, string $ref): JsonResponse
    {
        $claim = Claim::where('ref', $ref)
            ->where('tenant_id', $request->user()->tenant_id)
            ->firstOrFail();

        return response()->json(['claim' => $this->formatClaim($claim)]);
    }

    public function updateStatus(Request $request, string $ref): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:under_review,info_requested,approved,rejected,paid'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        $claim = Claim::where('ref', $ref)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $oldStatus = $claim->status;

        $claim->update(['status' => $request->status]);

        ClaimAuditLog::create([
            'claim_id' => $claim->id,
            'actor_id' => $user->id,
            'action' => 'status_changed',
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'note' => $request->note,
            'created_at' => now(),
        ]);

        // Queue SMS notification
        Notification::create([
            'user_id' => $claim->claimant_id,
            'claim_id' => $claim->id,
            'channel' => 'sms',
            'message' => "Your claim {$ref} status updated to: {$request->status}",
            'status' => 'pending',
        ]);

        // TODO: dispatch SendSmsNotification::dispatch($notification)

        return response()->json(['claim' => $this->formatClaim($claim->fresh())]);
    }

    private function generateRef(): string
    {
        $date = now()->format('Ymd');
        do {
            $suffix = strtoupper(Str::random(4));
            $ref = "CLM-{$date}-{$suffix}";
        } while (Claim::where('ref', $ref)->exists());

        return $ref;
    }

    private function formatClaim(Claim $claim): array
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
            'created_at' => $claim->created_at->toISOString(),
            'updated_at' => $claim->updated_at->toISOString(),
        ];
    }
}
