<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\OperatorBillingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly OperatorBillingService $billing,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $operator = $request->user();

        if (! $operator->isOperator()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! $this->billing->usesStripe()) {
            return redirect()
                ->route('operator.billing.index')
                ->with('status', 'Stripe billing is not configured — using mock plan on Billing.');
        }

        $plan = $this->billing->effectivePlan($operator);
        $creatorCount = \App\Models\Creator::query()->count();

        return view('settings.subscription', [
            'operator' => $operator,
            'plan' => $plan,
            'creatorCount' => $creatorCount,
            'hasProSubscription' => $this->billing->hasActiveProSubscription($operator),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $operator = $request->user();

        if (! $operator->isOperator()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! $this->billing->usesStripe()) {
            return back()->withErrors(['billing' => 'Stripe is not configured.']);
        }

        $priceId = $this->billing->proStripePriceId();

        if ($priceId === null) {
            return back()->withErrors(['billing' => 'Pro price is not configured.']);
        }

        if ($this->billing->hasActiveProSubscription($operator)) {
            return redirect()->route('settings.subscription')
                ->with('status', 'You already have an active Pro subscription.');
        }

        return $operator
            ->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('settings.subscription').'?checkout=success',
                'cancel_url' => route('settings.subscription').'?checkout=cancelled',
            ]);
    }

    public function portal(Request $request): RedirectResponse
    {
        $operator = $request->user();

        if (! $operator->isOperator()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! $this->billing->usesStripe()) {
            return back()->withErrors(['billing' => 'Stripe is not configured.']);
        }

        return $operator->redirectToBillingPortal(route('settings.subscription'));
    }
}
