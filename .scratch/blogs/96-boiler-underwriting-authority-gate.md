---
title: "How to Build an Underwriting Authority Gate for High-Pressure Boilers"
published: true
description: "Implement multi-tier approval rules that escalate commercial boiler quotations when operating pressure, volume, or age exceed underwriter authority limits."
tags: "laravel, security, underwriting, authority-limits, authorization"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/96-boiler-underwriting-authority-gate.md"
---

Commercial engineering risks can easily exceed the technical expertise of junior underwriters. While an entry-level underwriter might be permitted to bind standard low-pressure hot water boilers, allowing them to bind a 60-bar supercritical chemical autoclave without senior approval creates severe balance-sheet exposure.

Building an Underwriting Authority Gate evaluates both financial limits and engineering thresholds (pressure, vessel age, and fleet volume) before permitting quote issuance. Let's see how.

## The Dual Exposure Model

Most financial authority engines evaluate only the total sum insured (e.g., maximum \$1,000,000). For Boiler and Pressure Vessel (BPV) policies, financial loss is directly correlated with mechanical energy:

1. **Stored Energy Risk**: Stored energy in a pressurized steam vessel scales with both volume ($V$) and pressure ($P$). A small 50-bar autoclave carries significantly more catastrophic blast risk than a giant 2-bar storage vessel.
2. **Metallurgical Age**: Boilers operating beyond 25 years require specialized non-destructive testing (NDT) reports and chief engineering officer approval.
3. **Hazardous Medium**: Boilers heating toxic, flammable, or caustic chemical media represent environmental exposures requiring reinsurance sign-off.

Let's build an authorization gate that evaluates both financial sums and engineering physics.

## Step 1: Define Technical Authority Limit Attributes

Add engineering authority criteria to your underwriter authority limit schema:

`database/migrations/2026_09_28_000003_add_engineering_thresholds_to_authority_limits.php:`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('underwriting_authority_limits', function (Blueprint $table) {
            $table->decimal('max_vessel_pressure_bar', 8, 2)->default(10.0);
            $table->integer('max_vessel_age_years')->default(15);
            $table->boolean('can_approve_hazardous_media')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('underwriting_authority_limits', function (Blueprint $table) {
            $table->dropColumn(['max_vessel_pressure_bar', 'max_vessel_age_years', 'can_approve_hazardous_media']);
        });
    }
};
```

This ensures underwriter permissions reflect mechanical boundaries alongside monetary limits.

## Step 2: Implement the Technical Authority Evaluator

Create a domain service that checks a quotation's equipment schedule against the underwriter's credentials:

`app/Services/BoilerAuthorityGate.php:`
```php
<?php

namespace App\Services;

use App\Models\Policy;
use App\Models\User;

class BoilerAuthorityGate
{
    /**
     * @return array{approved: bool, escalation_reasons: array<string>}
     */
    public function evaluate(Policy $policy, User $underwriter): array
    {
        $policy->loadMissing('vessels');
        $limits = $underwriter->authorityLimitFor('Engineering');

        if (!$limits) {
            return [
                'approved' => false,
                'escalation_reasons' => ['User has no approved engineering authority limits configured.'],
            ];
        }

        $reasons = [];

        // 1. Financial Sum Insured Check
        if ($policy->total_sum_insured > $limits->max_sum_insured_usd) {
            $reasons[] = "Total fleet sum insured (${$policy->total_sum_insured}) exceeds underwriter limit (${$limits->max_sum_insured_usd}).";
        }

        // 2. Technical Engineering Checks
        foreach ($policy->vessels as $vessel) {
            if ($vessel->max_working_pressure_bar > $limits->max_vessel_pressure_bar) {
                $reasons[] = "Vessel {$vessel->tag_number} pressure ({$vessel->max_working_pressure_bar} bar) exceeds maximum limit ({$limits->max_vessel_pressure_bar} bar).";
            }

            $vesselAge = now()->year - $vessel->year_of_manufacture;
            if ($vesselAge > $limits->max_vessel_age_years) {
                $reasons[] = "Vessel {$vessel->tag_number} age ({$vesselAge} years) exceeds authority threshold ({$limits->max_vessel_age_years} years).";
            }
        }

        return [
            'approved' => empty($reasons),
            'escalation_reasons' => $reasons,
        ];
    }
}
```

The gate inspects every vessel in the fleet schedule, preventing junior staff from binding high-pressure units even if the policy's dollar value is low.

## Step 3: Enforce the Gate in the Controller Action

Protect the policy issuance endpoint:

`app/Http/Controllers/BoilerPolicyController.php:`
```php
<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Services\BoilerAuthorityGate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoilerPolicyController extends Controller
{
    public function issue(Request $request, Policy $policy, BoilerAuthorityGate $gate): JsonResponse
    {
        $evaluation = $gate->evaluate($policy, $request->user());

        if (!$evaluation['approved']) {
            $policy->update([
                'status' => 'pending_senior_approval',
                'escalation_notes' => implode("\n", $evaluation['escalation_reasons']),
            ]);

            return response()->json([
                'status' => 'escalated',
                'message' => 'Policy requires Senior Underwriter escalation.',
                'reasons' => $evaluation['escalation_reasons'],
            ], 403);
        }

        $policy->update(['status' => 'issued', 'issued_at' => now()]);

        return response()->json([
            'status' => 'issued',
            'message' => 'Boiler policy issued successfully.',
        ]);
    }
}
```

When an evaluation fails, the policy automatically transitions to an escalation queue with detailed technical reasons, allowing senior engineers to review the risk.

## What Can Go Wrong

- **Bypassing the Gate During Endorsement Modifications:** Underwriters might pass the gate during initial policy creation, then add an unapproved 50-bar reactor via an endorsement without triggering re-evaluation. Always execute the authority gate on mid-term endorsements as well as initial quotes.
- **Missing Historical Limit Audits:** If an underwriter's authority is adjusted from 10 bar to 30 bar, past quotes bound under earlier limits must retain an immutable audit trace of their authorization state at time of bind.

## Summary

Coupling financial limits with engineering physics (operating pressures and machinery age) ensures that specialized boiler risks are bound only by qualified underwriting authorities.

## Further Reading

- [Laravel Gates & Policy Authorization](https://laravel.com/docs/authorization)
- [Managing Engineering Risk in Commercial Property & Casualty](https://www.theinstitutes.org)
- [Enterprise Workflow Escalation Patterns](https://microservices.io/patterns/data/saga.html)

Next step: add senior escalation approval queues to your management dashboard.
