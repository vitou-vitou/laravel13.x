---
title: "How to Build Robust Underwriting Authority Limit Engines in Laravel"
published: true
description: "Model multi-tier underwriting approval limits, currency conversions, and automated escalation hierarchies in commercial insurance platforms."
tags: "laravel, architecture, insurance, underwriting, security"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/88-underwriting-authority-limits-engine.md"
---

Commercial insurance applications carry significant financial exposure. A junior underwriter should not be able to bind a multi-million-dollar property risk without managerial oversight. Without strict authority limits, human error or compromised accounts can commit the company to liabilities beyond approved risk tolerances.

Building an Underwriting Authority Limit engine enforces these rules systematically. It evaluates quotation sums insured, currency rates, product classes, and staff approval levels before allowing a policy to be issued. Let's see how.

## The Problem: Unchecked Commercial Exposure

In simple admin panels, role-based permissions often stop at binary checks:

```php
if ($user->can('issue-policy')) {
    $policy->status = 'issued';
    $policy->save();
}
```

This binary model fails to account for risk exposure: a user permitted to issue a \$50,000 personal auto policy should not be authorized to bind a \$10,000,000 industrial warehouse risk.

A robust authority limit engine evaluates whether the policy's total risk falls within the user's specific product-class authority limit before committing the transaction.

## Step 1: Model Underwriting Authority Limits in the Database

Create a database migration to track authority limits by user, product family, and maximum financial threshold:

`database/migrations/2026_09_28_000001_create_underwriter_limits_table.php:`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('underwriting_authority_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('product_family'); // 'Property', 'Engineering', 'Liability', 'Marine'
            $table->decimal('max_sum_insured_usd', 15, 2);
            $table->decimal('max_discount_percent', 5, 2)->default(0.0);
            $table->boolean('can_waive_deductible')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'product_family'], 'user_family_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('underwriting_authority_limits');
    }
};
```

This schema records maximum financial capacity in a normalized base currency (USD) alongside underwriting parameters like maximum allowable discount percentages.

## Step 2: Implement the Evaluation Engine

Build a domain service that checks a quotation against the active underwriter's authority limits:

`app/Services/UnderwritingAuthorityService.php:`
```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Quotation;
use App\Models\UnderwritingAuthorityLimit;
use DomainException;

class UnderwritingAuthorityService
{
    /**
     * Evaluates if an underwriter can independently bind a given quotation
     */
    public function canBindQuotation(User $user, Quotation $quotation): bool
    {
        // System administrators with override authority bypass standard checks
        if ($user->hasRole('Chief Underwriting Officer')) {
            return true;
        }

        $limit = UnderwritingAuthorityLimit::where('user_id', $user->id)
            ->where('product_family', $quotation->product_family)
            ->first();

        // If the user has no defined limit for this product family, reject
        if (!$limit) {
            return false;
        }

        // Convert quotation total sum insured into normalized USD
        $sumInsuredUsd = $this->normalizeToUsd($quotation->total_sum_insured, $quotation->currency);

        if ($sumInsuredUsd > $limit->max_sum_insured_usd) {
            return false;
        }

        if ($quotation->discount_percent > $limit->max_discount_percent) {
            return false;
        }

        return true;
    }

    public function assertCanBind(User $user, Quotation $quotation): void
    {
        if (!$this->canBindQuotation($user, $quotation)) {
            throw new DomainException(
                "Quotation exposure exceeds underwriter's authorized limit for {$quotation->product_family}."
            );
        }
    }

    protected function normalizeToUsd(float $amount, string $currency): float
    {
        if ($currency === 'USD') {
            return $amount;
        }

        // Fetch current exchange rate from cache
        $rate = cache()->get("fx_rate_{$currency}_USD", 1.0);
        return $amount * $rate;
    }
}
```

The service validates that both the total sum insured and the proposed discount fall within the underwriter's allowed boundaries.

## Step 3: Enforce Authority Limits in Action Controllers

Integrate the authority evaluation directly into the policy issuance workflow:

`app/Http/Controllers/PolicyIssuanceController.php:`
```php
<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Services\UnderwritingAuthorityService;
use App\Services\PolicyIssuanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DomainException;

class PolicyIssuanceController extends Controller
{
    public function __construct(
        protected UnderwritingAuthorityService $authorityService,
        protected PolicyIssuanceService $issuanceService
    ) {}

    public function issue(Request $request, string $quotationId): JsonResponse
    {
        $quotation = Quotation::findOrFail($quotationId);

        try {
            // Guard: Assert underwriter authority before proceeding
            $this->authorityService->assertCanBind($request->user(), $quotation);

            $policy = $this->issuanceService->issueFromQuotation($quotation, $request->user());

            return response()->json([
                'message' => 'Policy issued successfully.',
                'policy_number' => $policy->policy_number,
            ]);
        } catch (DomainException $e) {
            // Flag quotation as requiring managerial escalation
            $quotation->update(['requires_escalation' => true]);

            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'escalation_required',
            ], 422);
        }
    }
}
```

When an underwriter's limits are exceeded, the system automatically flags the quotation for management review rather than rejecting it outright.

## What Can Go Wrong

- **Ignoring Currency Normalization**: Comparing a local currency value (e.g., 40,000,000 KHR) directly against a USD threshold (\$10,000) results in false rejections or massive unauthorized exposures. Always normalize currencies to a shared base before evaluating limits.
- **Race Conditions on Concurrent Endorsements**: If two underwriters adjust endorsements on the same policy concurrently, their combined exposure might exceed approved limits. Always wrap exposure calculations in database row locks (`lockForUpdate`).

## Summary

Enforcing underwriting authority limits is critical for commercial insurance platforms. By modeling limits by product family, normalizing currency values, and verifying thresholds within domain services, applications prevent unauthorized exposure while keeping approval workflows running smoothly.

## Further Reading

- [Domain-Driven Design: Guard Clauses](https://martinfowler.com/bliki/GuardClause.html)
- [Managing Database Row Locks in Laravel](https://laravel.com/docs/queries#pessimistic-locking)
- [Enterprise Risk Management for Underwriting Portfolios](https://www.soa.org/sections/erm/)

Incorporate an authority limit engine into your underwriting workflow to protect your platform against unauthorized financial commitments.
