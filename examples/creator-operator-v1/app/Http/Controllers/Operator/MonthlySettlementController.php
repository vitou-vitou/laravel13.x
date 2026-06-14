<?php

namespace App\Http\Controllers\Operator;

use App\Enums\PayoutStatus;
use App\Enums\SettlementPlatform;
use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\MonthlySettlement;
use App\Services\SettlementCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MonthlySettlementController extends Controller
{
    public function __construct(
        protected SettlementCalculator $calculator,
    ) {}

    public function index(Creator $creator): View
    {
        $this->authorize('view', $creator);

        $settlements = $creator->monthlySettlements()->latest('period_start')->get();

        return view('operator.settlement.index', compact('creator', 'settlements'));
    }

    public function create(Creator $creator): View
    {
        $this->authorize('update', $creator);

        return view('operator.settlement.create', [
            'creator' => $creator,
            'platforms' => SettlementPlatform::cases(),
            'payoutStatuses' => PayoutStatus::cases(),
            'preview' => $this->calculator->calculate(100, 50000, 120000, 15, 0),
        ]);
    }

    public function store(Request $request, Creator $creator): RedirectResponse
    {
        $this->authorize('update', $creator);

        $validated = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'platform' => ['required', Rule::enum(SettlementPlatform::class)],
            'gross_payout_local' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'payout_status' => ['required', Rule::enum(PayoutStatus::class)],
            's_views' => ['required', 'integer', 'min:0'],
            't_views' => ['required', 'integer', 'min:0'],
            'commission_rate_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'monthly_ops_fee' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $computed = $this->calculator->calculate(
            (float) $validated['gross_payout_local'],
            (int) $validated['s_views'],
            (int) $validated['t_views'],
            (float) $validated['commission_rate_pct'],
            (float) $validated['monthly_ops_fee'],
        );

        MonthlySettlement::query()->create([
            ...$validated,
            ...$computed,
            'creator_id' => $creator->id,
        ]);

        return redirect()
            ->route('operator.creators.settlement.index', $creator)
            ->with('status', 'Monthly settlement saved.');
    }
}
