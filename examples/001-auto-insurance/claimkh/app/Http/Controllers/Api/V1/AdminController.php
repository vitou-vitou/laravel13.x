<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function tenants(Request $request): JsonResponse
    {
        $tenants = Tenant::withCount('users', 'claims')->get();
        return response()->json(['tenants' => $tenants]);
    }

    public function storeTenant(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:100', 'unique:tenants,subdomain', 'regex:/^[a-z0-9-]+$/'],
        ]);

        $tenant = Tenant::create([
            'name' => $request->name,
            'subdomain' => $request->subdomain,
            'config' => [
                'sla' => ['collision' => 7, 'theft' => 14, 'injury' => 10],
                'required_docs' => [
                    'collision' => ['driver_license', 'vehicle_registration', 'police_report', 'photo'],
                    'theft' => ['driver_license', 'vehicle_registration', 'police_report'],
                    'injury' => ['driver_license', 'vehicle_registration', 'police_report', 'photo'],
                ],
            ],
        ]);

        return response()->json(['tenant' => $tenant], 201);
    }

    public function updateTenantConfig(Request $request, int $tenantId): JsonResponse
    {
        $request->validate([
            'sla.collision' => ['nullable', 'integer', 'min:1', 'max:365'],
            'sla.theft' => ['nullable', 'integer', 'min:1', 'max:365'],
            'sla.injury' => ['nullable', 'integer', 'min:1', 'max:365'],
            'required_docs.collision' => ['nullable', 'array'],
            'required_docs.theft' => ['nullable', 'array'],
            'required_docs.injury' => ['nullable', 'array'],
        ]);

        $tenant = Tenant::findOrFail($tenantId);
        $config = $tenant->config ?? [];

        if ($request->has('sla')) {
            $config['sla'] = array_merge($config['sla'] ?? [], $request->sla);
        }

        if ($request->has('required_docs')) {
            $config['required_docs'] = array_merge($config['required_docs'] ?? [], $request->required_docs);
        }

        $tenant->update(['config' => $config]);

        return response()->json(['tenant' => $tenant->fresh()]);
    }

    public function claims(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Claim::where('tenant_id', $user->tenant_id)
            ->with('claimant', 'adjuster');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $claims = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($claims);
    }

    public function assignAdjuster(Request $request, string $ref): JsonResponse
    {
        $request->validate([
            'adjuster_id' => ['required', 'integer'],
        ]);

        $user = $request->user();
        $claim = Claim::where('ref', $ref)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $adjuster = User::where('id', $request->adjuster_id)
            ->where('tenant_id', $user->tenant_id)
            ->where('role', 'adjuster')
            ->where('is_active', true)
            ->firstOrFail();

        $claim->update(['adjuster_id' => $adjuster->id]);

        return response()->json(['claim' => ['ref' => $claim->ref, 'adjuster_id' => $adjuster->id]]);
    }

    public function stats(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $byStatus = Claim::where('tenant_id', $tenantId)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $byType = Claim::where('tenant_id', $tenantId)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        $avgResolutionDays = Claim::where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'rejected', 'paid'])
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');

        $rejectionRate = Claim::where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'rejected'])
            ->selectRaw('ROUND(SUM(status = "rejected") / COUNT(*) * 100, 2) as rate')
            ->value('rate');

        return response()->json([
            'by_status' => $byStatus,
            'by_type' => $byType,
            'avg_resolution_days' => round($avgResolutionDays ?? 0, 1),
            'rejection_rate_percent' => $rejectionRate ?? 0,
        ]);
    }

    public function storeUser(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+855[0-9]{8,9}$/'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'in:adjuster,admin'],
        ]);

        $tenantId = $request->user()->tenant_id;

        if (User::where('tenant_id', $tenantId)->where('phone', $request->phone)->exists()) {
            return response()->json(['error' => 'phone_taken'], 409);
        }

        $user = User::create([
            'tenant_id' => $tenantId,
            'phone' => $request->phone,
            'name' => $request->name,
            'role' => $request->role,
            'is_active' => true,
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function deactivateUser(Request $request, int $userId): JsonResponse
    {
        $user = User::where('id', $userId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->firstOrFail();

        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        return response()->json(['message' => 'user_deactivated']);
    }
}
