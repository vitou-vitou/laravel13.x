<?php

namespace App\Http\Controllers\Api;

use App\Enums\ClaimStatus;
use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Policy;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $this->policyholder($request);
        $data = $this->validateDraft($request);
        $this->ensureOwnedActivePolicy($user, $data['policy_id'] ?? null, $data['incident_at'] ?? null);

        $claim = Claim::create([
            ...$data,
            'policyholder_user_id' => $user->id,
        ]);

        return response()->json(['data' => $claim], 201);
    }

    public function update(Request $request, Claim $claim): JsonResponse
    {
        $user = $this->policyholder($request);
        $this->ensureOwner($claim, $user);

        $data = $this->validateDraft($request);
        $incidentAt = $data['incident_at'] ?? $claim->incident_at;
        $this->ensureOwnedActivePolicy($user, $data['policy_id'] ?? $claim->policy_id, $incidentAt);

        $claim->fill($data)->save();

        return response()->json(['data' => $claim->fresh()]);
    }

    public function submit(Request $request, Claim $claim): JsonResponse
    {
        $user = $this->policyholder($request);
        $this->ensureOwner($claim, $user);

        if ($claim->status !== ClaimStatus::Draft) {
            throw ValidationException::withMessages(['status' => ['Only draft claims can be submitted.']]);
        }

        $data = validator($claim->only([
            'policy_id',
            'incident_at',
            'location',
            'incident_type',
            'description',
        ]), $this->submissionRules())->validate();

        $this->ensureOwnedActivePolicy($user, $data['policy_id'], $data['incident_at']);

        $claim->submit($this->nextReference());
        $claim->save();

        return response()->json(['data' => $claim->fresh()]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDraft(Request $request): array
    {
        return $request->validate([
            'policy_id' => ['nullable', 'integer'],
            'incident_at' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'incident_type' => ['nullable', Rule::in(['collision', 'theft', 'third_party', 'other'])],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function submissionRules(): array
    {
        return [
            'policy_id' => ['required', 'integer'],
            'incident_at' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'incident_type' => ['required', Rule::in(['collision', 'theft', 'third_party', 'other'])],
            'description' => ['required', 'string', 'max:5000'],
        ];
    }

    private function policyholder(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User && $user->isPolicyholder(), 403);

        return $user;
    }

    private function ensureOwner(Claim $claim, User $user): void
    {
        abort_unless($claim->policyholder_user_id === $user->id, 403);
    }

    private function ensureOwnedActivePolicy(User $user, ?int $policyId, mixed $incidentAt): void
    {
        if ($policyId === null) {
            return;
        }

        $activeOn = $incidentAt ? now()->parse($incidentAt) : now();
        $policyExists = Policy::query()
            ->active($activeOn)
            ->forPolicyholder($user)
            ->whereKey($policyId)
            ->exists();

        if (! $policyExists) {
            throw ValidationException::withMessages([
                'policy_id' => ['Select one of your active policies.'],
            ]);
        }
    }

    private function nextReference(): string
    {
        do {
            $reference = 'CLM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Claim::withoutGlobalScopes()->where('reference', $reference)->exists());

        return $reference;
    }
}
