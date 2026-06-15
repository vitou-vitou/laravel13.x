<?php

namespace App\Http\Controllers\Operator;

use App\Enums\PublishStatus;
use App\Enums\MusicPolicy;
use App\Enums\ServiceTier;
use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CreatorController extends Controller
{
    public function index(): View
    {
        $creators = Creator::query()
            ->withCount([
                'publishLogEntries as pending_count' => fn ($q) => $q->where('status', PublishStatus::PendingApproval),
            ])
            ->orderBy('handle')
            ->get();

        return view('operator.creators.index', compact('creators'));
    }

    public function create(): View
    {
        return view('operator.creators.create', [
            'tiers' => ServiceTier::cases(),
            'musicPolicies' => MusicPolicy::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $operator = $request->user();
        $limit = $operator->creatorLimit();

        if ($limit > 0 && Creator::query()->count() >= $limit) {
            return back()
                ->withInput()
                ->withErrors([
                    'handle' => "Creator limit reached ({$limit}) on {$operator->operator_plan?->label()} plan. Upgrade on Billing.",
                ]);
        }

        $validated = $request->validate([
            'handle' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:creators,handle'],
            'tiktok_url' => ['required', 'url', 'max:500'],
            'tier' => ['required', Rule::enum(ServiceTier::class)],
            'music_policy' => ['required', Rule::enum(MusicPolicy::class)],
            'youtube_manager_email' => ['nullable', 'email', 'max:255'],
            'meta_manager_email' => ['nullable', 'email', 'max:255'],
            'onboarding_notes' => ['nullable', 'string', 'max:5000'],
            'creator_name' => ['nullable', 'string', 'max:255'],
            'creator_email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
        ]);

        $userId = null;

        if (! empty($validated['creator_email'])) {
            $creatorUser = User::query()->create([
                'name' => $validated['creator_name'] ?? $validated['handle'],
                'email' => $validated['creator_email'],
                'password' => Hash::make('password'),
                'role' => UserRole::Creator,
            ]);
            $userId = $creatorUser->id;
        }

        $creator = Creator::query()->create([
            'user_id' => $userId,
            'handle' => $validated['handle'],
            'tiktok_url' => $validated['tiktok_url'],
            'tier' => $validated['tier'],
            'music_policy' => $validated['music_policy'],
            'youtube_manager_email' => $validated['youtube_manager_email'] ?? null,
            'meta_manager_email' => $validated['meta_manager_email'] ?? null,
            'onboarding_notes' => $validated['onboarding_notes'] ?? null,
        ]);

        return redirect()
            ->route('operator.creators.show', $creator)
            ->with('status', 'Creator onboarded.');
    }

    public function show(Request $request, Creator $creator): View
    {
        $this->authorize('view', $creator);

        $statusFilter = $request->query('status');
        $entriesQuery = $creator->publishLogEntries()->latest();

        if ($statusFilter && PublishStatus::tryFrom($statusFilter)) {
            $entriesQuery->where('status', $statusFilter);
        }

        $entries = $entriesQuery->limit(50)->get();

        $statusCounts = $creator->publishLogEntries()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('operator.creators.show', [
            'creator' => $creator,
            'entries' => $entries,
            'statusFilter' => $statusFilter,
            'statusCounts' => $statusCounts,
            'statuses' => PublishStatus::cases(),
        ]);
    }

    public function edit(Creator $creator): View
    {
        $this->authorize('update', $creator);

        return view('operator.creators.edit', [
            'creator' => $creator,
            'tiers' => ServiceTier::cases(),
            'musicPolicies' => MusicPolicy::cases(),
        ]);
    }

    public function update(Request $request, Creator $creator): RedirectResponse
    {
        $this->authorize('update', $creator);

        $validated = $request->validate([
            'tiktok_url' => ['required', 'url', 'max:500'],
            'tier' => ['required', Rule::enum(ServiceTier::class)],
            'music_policy' => ['required', Rule::enum(MusicPolicy::class)],
            'youtube_manager_email' => ['nullable', 'email', 'max:255'],
            'meta_manager_email' => ['nullable', 'email', 'max:255'],
            'last_run_date' => ['nullable', 'date'],
            'onboarding_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $creator->update($validated);

        return redirect()
            ->route('operator.creators.show', $creator)
            ->with('status', 'Creator updated.');
    }
}
