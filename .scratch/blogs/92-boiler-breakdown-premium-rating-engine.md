---
title: "How to Calculate Boiler Explosion and Machinery Breakdown Premiums in PHP"
published: true
description: "Build an actuarial rating engine for boiler explosion, metallurgical fatigue, and surrounding property damage surcharges in PHP 8."
tags: "php, laravel, actuarial, insurance, math"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/92-boiler-breakdown-premium-rating-engine.md"
---

Boiler and Pressure Vessel rating differs from standard fire and property underwriting. The base premium must account for vessel volume, operating pressure, heating method (fired versus unfired), machinery breakdown, and catastrophic surrounding property damage caused by shockwaves.

Building an actuarial calculation service in PHP gives your underwriting platform deterministic, reproducible pricing rules that can be audited by risk reinsurers. Let's see how.

## The Mathematical Rating Model

A commercial BPV rating algorithm computes premium across three distinct exposure layers:

1. **Base Vessel Risk ($P_{\text{base}}$)**: Derived from the sum insured scaled by the vessel type base rate.
2. **Pressure & Capacity Factor ($F_{\text{press}}$)**: Loading factor applied when working pressures exceed standard utility baselines (e.g., >10 bar).
3. **Surrounding Property & Third-Party Extension ($P_{\text{ext}}$)**: Coverage for damage to neighbouring plant buildings if the vessel bursts.

```text
Total Premium = (Sum Insured * Base Rate * Pressure Factor * Age Factor) + Surrounding Property Loading
```

Let's build a dedicated, testable rating service that implements this formula.

## Step 1: Create the Rating Value Objects

Define strongly typed parameter objects to avoid primitive obsession:

`app/ValueObjects/BoilerRatingProfile.php:`
```php
<?php

namespace App\ValueObjects;

readonly class BoilerRatingProfile
{
    public function __construct(
        public string $vesselType,
        public float $sumInsuredUsd,
        public float $maxWorkingPressureBar,
        public int $ageYears,
        public float $surroundingPropertyLimitUsd,
        public bool $includeBusinessInterruption = false,
    ) {}
}
```

The readonly value object encapsulates all technical parameters required for rating, making calculations side-effect free.

## Step 2: Implement the Actuarial Calculation Engine

Create the rating engine with explicit actuarial steps:

`app/Services/BoilerRatingEngine.php:`
```php
<?php

namespace App\Services;

use App\ValueObjects\BoilerRatingProfile;

class BoilerRatingEngine
{
    public function calculatePremium(BoilerRatingProfile $profile): array
    {
        // 1. Base Rate by Vessel Type (per mille: per $1,000 of sum insured)
        $baseRatePermille = match ($profile->vesselType) {
            'steam_boiler' => 4.50,
            'fired_pressure_vessel' => 3.20,
            'unfired_receiver' => 1.80,
            default => 2.50,
        };

        $basePremium = ($profile->sumInsuredUsd * $baseRatePermille) / 1000;

        // 2. Pressure Loading Factor
        $pressureFactor = 1.0;
        if ($profile->maxWorkingPressureBar > 25.0) {
            $pressureFactor = 1.35; // +35% for extreme high pressure
        } elseif ($profile->maxWorkingPressureBar > 10.0) {
            $pressureFactor = 1.15; // +15% for medium high pressure
        }

        // 3. Metallurgical Age Loading
        $ageFactor = 1.0;
        if ($profile->ageYears > 20) {
            $ageFactor = 1.40; // +40% fatigue risk
        } elseif ($profile->ageYears > 10) {
            $ageFactor = 1.20; // +20%
        }

        $vesselNetPremium = $basePremium * $pressureFactor * $ageFactor;

        // 4. Surrounding Property Explosion Extension (0.75 per mille)
        $surroundingPropertyPremium = ($profile->surroundingPropertyLimitUsd * 0.75) / 1000;

        // 5. Business Interruption Surcharge (optional 25% loading)
        $biPremium = $profile->includeBusinessInterruption ? ($vesselNetPremium * 0.25) : 0.0;

        $totalPremium = round($vesselNetPremium + $surroundingPropertyPremium + $biPremium, 2);

        return [
            'base_premium' => round($basePremium, 2),
            'pressure_loading' => round(($vesselNetPremium - $basePremium), 2),
            'surrounding_property_premium' => round($surroundingPropertyPremium, 2),
            'business_interruption_premium' => round($biPremium, 2),
            'total_premium_usd' => $totalPremium,
        ];
    }
}
```

The engine separates each actuarial surcharge into an itemized breakdown. Underwriters can explain exactly why a 22-year-old steam boiler incurs a higher premium.

## Step 3: Write a Pest Test to Verify Calculations

Lock the actuarial formula with an automated test:

`tests/Unit/BoilerRatingEngineTest.php:`
```php
<?php

use App\Services\BoilerRatingEngine;
use App\ValueObjects\BoilerRatingProfile;

it('calculates expected premium with pressure and age loading', function () {
    $engine = new BoilerRatingEngine();
    
    // Steam boiler: $100,000, 15 bar (>10 bar = 1.15x), 12 years old (>10 yrs = 1.20x)
    // Base = 100,000 * 4.5 / 1000 = $450
    // Loaded = 450 * 1.15 * 1.20 = $621.00
    // Surrounding property: $50,000 * 0.75 / 1000 = $37.50
    // Total = 621.00 + 37.50 = $658.50
    $profile = new BoilerRatingProfile(
        vesselType: 'steam_boiler',
        sumInsuredUsd: 100000.0,
        maxWorkingPressureBar: 15.0,
        ageYears: 12,
        surroundingPropertyLimitUsd: 50000.0,
        includeBusinessInterruption: false
    );

    $result = $engine->calculatePremium($profile);

    expect($result['total_premium_usd'])->toBe(658.50);
});
```

The test validates the compound loading factors, guaranteeing future tariff updates will not alter historical quotes unexpectedly.

## What Can Go Wrong

- **Floating Point Rounding Errors:** Using standard floating-point operations across nested rate multiplications can accumulate rounding discrepancies. Always use `round(..., 2)` at step boundaries or `bcmath` for high-volume billing pipelines.
- **Hidden Minimum Premium Clauses:** Reinsurance treaties almost universally require a minimum retained premium (e.g., \$250 per policy) regardless of vessel size. Ensure your engine checks against statutory minimum premium floors.

## Summary

Decoupling BPV premium logic into a specialized calculation service with explicit value objects makes underwriting math transparent, inspectable, and auditable by reinsurers.

## Further Reading

- [PHP 8 Readonly Classes and Types](https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.readonly-classes)
- [Pest PHP Testing Framework](https://pestphp.com)
- [Insurance Rating Algorithms and Actuarial Principles](https://www.actuaries.org)

Next step: connect this rating engine to your Direct Book quotation calculation endpoint.
