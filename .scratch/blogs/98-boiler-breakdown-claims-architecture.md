---
title: "How to Model Boiler Explosion and Machinery Breakdown Claims in Laravel"
published: true
description: "Design an event-driven claims intake architecture for commercial boiler explosions, machinery breakdown repair estimates, and business interruption losses."
tags: "laravel, claims, domain-driven-design, events, insurance"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/98-boiler-breakdown-claims-architecture.md"
---

Boiler and Pressure Vessel claims are among the most financially catastrophic events in commercial insurance. A sudden boiler rupture causes physical destruction to surrounding plant structures, expensive equipment repairs, and significant business interruption losses while replacement units are fabricated.

Building a domain-driven claims intake architecture in Laravel separates initial emergency reserve calculations, engineering forensic adjustments, and subrogation recoveries into clear, auditable states. Let's see how.

## The Tripartite Nature of Boiler Losses

Unlike an auto accident where damages map directly to vehicle parts, an industrial boiler loss splits into three distinct claim components:

1. **Own Machinery Breakdown ($L_{\text{own}}$)**: Direct physical damage to the vessel, heating tubes, burners, and pressure relief valves.
2. **Surrounding Plant Property ($L_{\text{surr}}$)**: Collapsed boiler house roofs, shattered concrete foundations, and damaged nearby manufacturing lines.
3. **Time Element / Business Interruption ($L_{\text{bi}}$)**: Daily lost revenue and fixed plant overhead incurred while specialized industrial replacement boilers are manufactured and shipped.

Capturing these streams under dedicated claim sub-items ensures reserve adjustments remain transparent.

## Step 1: Design the Claims Schema

Create migrations for the primary claim record and its individual loss components:

`database/migrations/2026_09_28_000004_create_boiler_claims_table.php:`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boiler_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained();
            $table->foreignId('vessel_id')->constrained('boiler_pressure_vessels');
            $table->string('claim_number')->unique();
            $table->date('incident_date');
            $table->string('status'); // 'registered', 'investigating', 'approved', 'settled'
            $table->decimal('reserve_own_damage_usd', 15, 2)->default(0.0);
            $table->decimal('reserve_surrounding_property_usd', 15, 2)->default(0.0);
            $table->decimal('reserve_business_interruption_usd', 15, 2)->default(0.0);
            $table->decimal('total_settled_usd', 15, 2)->default(0.0);
            $table->text('root_cause_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boiler_claims');
    }
};
```

This structure allows adjusters to update reserves on business interruption independently of physical machinery repair costs.

## Step 2: Implement the Domain Claim Intake Action

Use a single-action class to register the loss and emit domain events:

`app/Actions/RegisterBoilerClaimAction.php:`
```php
<?php

namespace App\Actions;

use App\Events\BoilerClaimRegisteredEvent;
use App\Models\BoilerClaim;
use App\Models\BoilerPressureVessel;
use App\Models\Policy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RegisterBoilerClaimAction
{
    public function execute(
        Policy $policy,
        BoilerPressureVessel $vessel,
        Carbon $incidentDate,
        float $estimatedMachineryDamage,
        float $estimatedSurroundingDamage,
        float $estimatedBiLoss
    ): BoilerClaim {
        return DB::transaction(function () use (
            $policy,
            $vessel,
            $incidentDate,
            $estimatedMachineryDamage,
            $estimatedSurroundingDamage,
            $estimatedBiLoss
        ) {
            // Verify vessel belongs to policy
            if ($vessel->policy_id !== $policy->id) {
                throw new \InvalidArgumentException("Vessel does not belong to specified policy.");
            }

            // Verify incident occurred during policy active period
            if ($incidentDate->lt($policy->start_date) || $incidentDate->gt($policy->end_date)) {
                throw new \DomainException("Incident date is outside the active policy period.");
            }

            $claimNumber = "CLM-BPV-" . now()->format('Ymd') . "-" . str_pad((string) (BoilerClaim::count() + 1), 4, '0', STR_PAD_LEFT);

            $claim = BoilerClaim::create([
                'policy_id' => $policy->id,
                'vessel_id' => $vessel->id,
                'claim_number' => $claimNumber,
                'incident_date' => $incidentDate,
                'status' => 'registered',
                'reserve_own_damage_usd' => $estimatedMachineryDamage,
                'reserve_surrounding_property_usd' => $estimatedSurroundingDamage,
                'reserve_business_interruption_usd' => $estimatedBiLoss,
            ]);

            // Dispatch event for emergency engineering loss adjusters
            event(new BoilerClaimRegisteredEvent($claim));

            return $claim;
        });
    }
}
```

The action validates date boundaries, generates standard claim numbering, and dispatches a domain event that triggers immediate forensic loss adjustment notifications.

## Step 3: Listen for Claim Registration

Handle downstream notifications to reinsurance and engineering loss surveyors:

`app/Listeners/NotifyEngineeringLossSurveyorListener.php:`
```php
<?php

namespace App\Listeners;

use App\Events\BoilerClaimRegisteredEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyEngineeringLossSurveyorListener implements ShouldQueue
{
    public function handle(BoilerClaimRegisteredEvent $event): void
    {
        $claim = $event->claim->load(['policy', 'vessel']);

        Log::channel('claims')->info("Dispatched emergency engineering surveyor for claim {$claim->claim_number}", [
            'vessel' => $claim->vessel->tag_number,
            'operating_pressure' => $claim->vessel->max_working_pressure_bar,
            'initial_reserves_total' => $claim->reserve_own_damage_usd + $claim->reserve_surrounding_property_usd + $claim->reserve_business_interruption_usd,
        ]);
    }
}
```

This ensures field adjusters receive immediate dispatch instructions while reserving estimates are logged securely.

## What Can Go Wrong

- **Ignoring Statutory Certificate Status at Loss Date:** If the vessel's certificate was expired on the day of the explosion, the insurer must issue a Reservation of Rights letter prior to making repair commitments. Verify statutory certificate validity in the claims registration listener.
- **Under-reserving Business Interruption:** Sourcing custom high-pressure steam boilers can take 6 to 12 months. Setting an initial BI reserve based on standard supply chains creates severe reserving adjustments later.

## Summary

Structuring boiler claims into distinct own-damage, surrounding-property, and business interruption components gives adjusters and reinsurers granular visibility into complex industrial losses.

## Further Reading

- [Domain-Driven Design Actions in Laravel](https://freek.dev/1371-refactoring-to-actions)
- [Managing Reinsurance Reserving in Large Engineering Losses](https://www.swissre.com)
- [Laravel Events and Listeners](https://laravel.com/docs/events)

Next step: connect your claims registration form to this action.
