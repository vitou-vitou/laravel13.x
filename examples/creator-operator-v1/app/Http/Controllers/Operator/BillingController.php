<?php

namespace App\Http\Controllers\Operator;

use App\Enums\OperatorPlan;
use App\Http\Controllers\Controller;
use App\Services\OperatorBillingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function __construct(
        private readonly OperatorBillingService $billing,
    ) {}

    public function index(Request $request): View
    {
        $operator = $request->user();
        $plan = $this->billing->effectivePlan($operator);
        $creatorCount = \App\Models\Creator::query()->count();
        $plans = config('operator-billing.plans', []);

        return view('operator.billing.index', [
            'operator' => $operator,
            'plan' => $plan,
            'creatorCount' => $creatorCount,
            'plans' => $plans,
            'usesStripe' => $this->billing->usesStripe(),
            'hasProSubscription' => $this->billing->hasActiveProSubscription($operator),
        ]);
    }

    public function updatePlan(Request $request): RedirectResponse
    {
        if ($this->billing->usesStripe()) {
            return redirect()
                ->route('settings.subscription')
                ->with('status', 'Plans are managed through Stripe on the subscription page.');
        }

        $validated = $request->validate([
            'operator_plan' => ['required', Rule::enum(OperatorPlan::class)],
        ]);

        $request->user()->update([
            'operator_plan' => $validated['operator_plan'],
        ]);

        return back()->with('status', 'Plan updated (mock — no Stripe charge in Track A).');
    }
}
