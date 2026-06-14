<?php

namespace App\Http\Controllers\Operator;

use App\Enums\OperatorPlan;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $operator = $request->user();
        $plan = $operator->operator_plan ?? OperatorPlan::Starter;
        $creatorCount = \App\Models\Creator::query()->count();
        $plans = config('operator-billing.plans', []);

        return view('operator.billing.index', compact('operator', 'plan', 'creatorCount', 'plans'));
    }

    public function updatePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'operator_plan' => ['required', Rule::enum(OperatorPlan::class)],
        ]);

        $request->user()->update([
            'operator_plan' => $validated['operator_plan'],
        ]);

        return back()->with('status', 'Plan updated (mock — no Stripe charge in Track A).');
    }
}
