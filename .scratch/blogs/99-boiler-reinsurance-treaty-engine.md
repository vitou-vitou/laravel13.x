---
title: "How to Model Boiler Reinsurance Treaties and Excess of Loss in Laravel"
published: true
description: "Calculate quota share cessions, surplus lines, and per-vessel excess of loss reinsurance retentions for high-capacity industrial boiler portfolios in Laravel."
tags: "laravel, reinsurance, finance, architecture, actuarial"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/99-boiler-reinsurance-treaty-engine.md"
---

Commercial industrial plants often carry hundreds of millions of dollars in pressurized vessel exposure. No primary insurance carrier retains this level of concentrated explosion risk on its own balance sheet. Instead, policies are ceded through reinsurance treaties: Quota Share, Surplus Lines, and Excess of Loss (XOL).

Building a Reinsurance Treaty Engine in Laravel computes exact primary retentions and reinsurer cessions at the moment a boiler quote is bound. Let's see how.

## The Reinsurance Allocation Cascade

When an underwriter issues a \$20,000,000 boiler fleet policy, the risk cascades through multiple treaty layers:

1. **Carrier Net Retention ($R_{\text{net}}$)**: The primary company's internal risk capacity (e.g., maximum \$2,000,000).
2. **Quota Share Treaty**: Proportional sharing of risk (e.g., 40% retained by carrier, 60% ceded to reinsurer up to treaty limit).
3. **Surplus Lines Treaty**: Accommodates sum insured exceeding the initial quota share limit.
4. **Facultative Reinsurance**: Individual open-market reinsurance placed specifically for mega-scale chemical plants.

Calculating this breakdown programmatically guarantees carrier solvency compliance and automates quarterly reinsurance borderaux reporting.

## Step 1: Define Reinsurance Treaty Rules

Create a model representing treaty agreements:

`app/Models/ReinsuranceTreaty.php:`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReinsuranceTreaty extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'carrier_max_retention_usd' => 'decimal:2',
        'quota_share_carrier_percent' => 'decimal:2',
        'quota_share_reinsurer_percent' => 'decimal:2',
        'surplus_capacity_usd' => 'decimal:2',
    ];
}
```

The treaty stores the mathematical limits agreed upon between the insurer and global reinsurers.

## Step 2: Implement the Reinsurance Cession Engine

Build the allocation calculation service:

`app/Services/BoilerReinsuranceCessionService.php:`
```php
<?php

namespace App\Services;

use App\Models\Policy;
use App\Models\ReinsuranceTreaty;

class BoilerReinsuranceCessionService
{
    /**
     * @return array{carrier_retention_usd: float, treaty_cession_usd: float, facultative_required_usd: float}
     */
    public function calculateCession(Policy $policy, ReinsuranceTreaty $treaty): array
    {
        $sumInsured = (float) $policy->total_sum_insured;
        $maxRetention = (float) $treaty->carrier_max_retention_usd;

        // 1. Determine Carrier Net Retained Share
        $carrierRetention = min($sumInsured * ($treaty->quota_share_carrier_percent / 100), $maxRetention);

        // 2. Determine Treaty Reinsurance Share
        $remainingRisk = max(0.0, $sumInsured - $carrierRetention);
        $treatyCapacity = (float) $treaty->surplus_capacity_usd;

        $treatyCession = min($remainingRisk, $treatyCapacity);

        // 3. Excess balance requires Facultative reinsurance
        $facultativeRequired = max(0.0, $remainingRisk - $treatyCession);

        return [
            'carrier_retention_usd' => round($carrierRetention, 2),
            'treaty_cession_usd' => round($treatyCession, 2),
            'facultative_required_usd' => round($facultativeRequired, 2),
        ];
    }
}
```

The service calculates exact risk distributions in dollars. If the policy exceeds standard treaty capacity, it flags the remaining amount as requiring facultative placement before binding.

## Step 3: Record Cession Allocations on Policy Issuance

Persist the treaty breakdown in the database:

`app/Actions/BindBoilerPolicyAction.php:`
```php
<?php

namespace App\Actions;

use App\Models\Policy;
use App\Models\ReinsuranceTreaty;
use App\Services\BoilerReinsuranceCessionService;
use Illuminate\Support\Facades\DB;

class BindBoilerPolicyAction
{
    public function __construct(
        protected BoilerReinsuranceCessionService $cessionService
    ) {}

    public function execute(Policy $policy, ReinsuranceTreaty $treaty): Policy
    {
        return DB::transaction(function () use ($policy, $treaty) {
            $breakdown = $this->cessionService->calculateCession($policy, $treaty);

            if ($breakdown['facultative_required_usd'] > 0 && !$policy->has_approved_facultative_cover) {
                throw new \DomainException("Policy exceeds treaty capacity by \${$breakdown['facultative_required_usd']}. Facultative reinsurance approval required.");
            }

            $policy->update([
                'status' => 'bound',
                'carrier_retained_sum_usd' => $breakdown['carrier_retention_usd'],
                'reinsurance_ceded_sum_usd' => $breakdown['treaty_cession_usd'],
                'facultative_sum_usd' => $breakdown['facultative_required_usd'],
                'bound_at' => now(),
            ]);

            return $policy;
        });
    }
}
```

The bind action stops any policy whose sum insured breaches treaty capacity unless an underwriter has already uploaded and approved a facultative reinsurance cover note.

## What Can Go Wrong

- **Currency Mismatch Between Primary Policies and Reinsurance Treaties:** Primary policies might be issued in local currencies (EUR, GBP, KHR) while reinsurance treaties settle exclusively in USD. Re-evaluating exchange rates at monthly bordereaux generation rather than policy bind time can introduce balance sheet currency drift. Always lock the FX rate at the bind date.
- **Neglecting Reinstatement Premiums on Claims:** Reinsurance treaties often include a mandatory reinstatement premium clause: when a catastrophic boiler loss consumes treaty capacity, the insurer must pay a prorated fee to reinstate coverage for the remainder of the year.

## Summary

Automating reinsurance cessions directly into the policy bind pipeline enforces portfolio solvency limits and eliminates manual spreadsheets when reporting engineering risk to treaty reinsurers.

## Further Reading

- [Fundamentals of Treaty and Facultative Reinsurance](https://www.reinsurance.org)
- [Financial Ledgers and Auditing in Complex Systems](https://martinfowler.com)
- [Laravel Eloquent Model Events](https://laravel.com/docs/eloquent#events)

Next step: build an exportable Reinsurance Bordereaux report for your finance team.
