---
title: "How to Build a Boiler Endorsement Engine for Equipment Substitutions"
published: true
description: "Model mid-term policy endorsements for adding, decommissioning, or swapping industrial pressure vessels with automated pro-rata premium adjustments."
tags: "laravel, architecture, insurance, endorsements, financial"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/94-boiler-endorsement-equipment-substitution.md"
---

Industrial manufacturing facilities rarely remain static during a twelve-month policy term. Plants install new auxiliary steam boilers, decommission obsolete air receivers, or upgrade operating capacity mid-year. Every physical equipment change requires an official policy endorsement.

Building an Endorsement Engine for equipment substitutions ensures that newly added vessels are covered immediately, removed vessels receive accurate pro-rata premium refunds, and historical risk audits remain immutable. Let's see how.

## The Endorsement Challenge

When a plant replaces an old 10-bar boiler with a brand new 25-bar unit mid-term:

1. **Active Exposure Shift**: The old vessel's risk ceases at 23:59:59 on the substitution date; the new vessel's coverage starts at 00:00:00.
2. **Pro-Rata Financial Balancing**: The unearned premium on the decommissioned vessel must credit against the additional premium charged for the higher-risk replacement unit.
3. **Audit Immutability**: Historical claims occurring prior to the endorsement date must continue referencing the original machinery schedule.

Mutating records in-place destroys this audit history. We must use an append-only endorsement ledger.

## Step 1: Create the Endorsement Record

Define an endorsement transaction model that records the substitution delta:

`database/migrations/2026_09_28_000002_create_boiler_endorsements_table.php:`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boiler_endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained();
            $table->string('endorsement_number')->unique();
            $table->date('effective_date');
            $table->string('type'); // 'substitute', 'addition', 'cancellation'
            $table->foreignId('decommissioned_vessel_id')->nullable()->constrained('boiler_pressure_vessels');
            $table->foreignId('replacement_vessel_id')->nullable()->constrained('boiler_pressure_vessels');
            $table->decimal('unearned_credit_usd', 15, 2)->default(0.0);
            $table->decimal('additional_premium_usd', 15, 2)->default(0.0);
            $table->decimal('net_endorsement_premium_usd', 15, 2)->default(0.0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boiler_endorsements');
    }
};
```

This table links old and new vessels while recording the exact financial adjustment resulting from the substitution.

## Step 2: Implement the Endorsement Service

Create the domain service that performs the pro-rata balancing:

`app/Services/BoilerEndorsementService.php:`
```php
<?php

namespace App\Services;

use App\Models\BoilerPressureVessel;
use App\Models\Policy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BoilerEndorsementService
{
    public function substituteVessel(
        Policy $policy,
        BoilerPressureVessel $oldVessel,
        BoilerPressureVessel $newVessel,
        Carbon $effectiveDate,
        float $oldVesselAnnualPremium,
        float $newVesselAnnualPremium
    ): array {
        return DB::transaction(function () use (
            $policy,
            $oldVessel,
            $newVessel,
            $effectiveDate,
            $oldVesselAnnualPremium,
            $newVesselAnnualPremium
        ) {
            $totalDays = $policy->start_date->diffInDays($policy->end_date);
            $remainingDays = $effectiveDate->diffInDays($policy->end_date);

            $proRataRatio = max(0, min(1, $remainingDays / $totalDays));

            // Calculate credit for old vessel & debit for new vessel
            $unearnedCredit = round($oldVesselAnnualPremium * $proRataRatio, 2);
            $additionalDebit = round($newVesselAnnualPremium * $proRataRatio, 2);
            $netPayable = round($additionalDebit - $unearnedCredit, 2);

            // Mark old vessel status
            $oldVessel->update(['status' => 'decommissioned', 'decommissioned_at' => $effectiveDate]);

            // Save endorsement record
            $endorsement = $policy->endorsements()->create([
                'endorsement_number' => "END-{$policy->policy_number}-" . ($policy->endorsements()->count() + 1),
                'effective_date' => $effectiveDate,
                'type' => 'substitute',
                'decommissioned_vessel_id' => $oldVessel->id,
                'replacement_vessel_id' => $newVessel->id,
                'unearned_credit_usd' => $unearnedCredit,
                'additional_premium_usd' => $additionalDebit,
                'net_endorsement_premium_usd' => $netPayable,
            ]);

            return [
                'endorsement' => $endorsement,
                'net_adjustment_usd' => $netPayable,
            ];
        });
    }
}
```

The service calculates remaining policy days deterministically and atomically updates vessel availability and financial ledgers inside a single database transaction.

## What Can Go Wrong

- **Backdated Endorsement Claims Collision:** If an underwriter processes a backdated decommissioning for a vessel that was involved in an active breakdown claim last week, simple date logic will corrupt claim liability. Validate that no active claims exist against the vessel within the backdated window before allowing decommissioning.
- **Leap Year Day Count Variance:** Calculating pro-rata fractions using 365 instead of the actual policy term days creates penny-drift between accounting and underwriting departments. Always use `diffInDays()` on the actual start and end dates.

## Summary

Handling mid-term machinery substitutions through dedicated endorsement events protects policy audit histories, automates pro-rata financial adjustments, and maintains accurate plant risk records.

## Further Reading

- [Laravel Database Transactions](https://laravel.com/docs/database#database-transactions)
- [Carbon DateTime Manipulation in PHP](https://carbon.nesbot.com/docs/)
- [Financial Ledger Design and Double-Entry Auditing](https://martinfowler.com/eaaDev/AccountingTransaction.html)

Next step: connect your frontend endorsement workflow to this service.
