---
title: "How to Model Boiler and Pressure Vessel Risks in Laravel"
published: true
description: "Model industrial pressure ratings, vessel inspection histories, and multi-vessel engineering risk aggregations in commercial Laravel insurance systems."
tags: "laravel, engineering, insurance, architecture, database"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/90-boiler-pressure-vessel-risk-modeling-laravel.md"
---

Boiler and Pressure Vessel (BPV) insurance requires domain modeling that generic property policies cannot accommodate. Standard property policies insure walls, roofs, and square footage. BPV policies insure pressurized containment units where failure causes catastrophic explosion, physical damage, and extended business interruption.

When modeling BPV underwriting in an ERP or policy management system, treating a boiler as a flat schedule item loses critical engineering variables: operating pressure (PSI/bar), manufacturer test certificates, safety valve limits, and mandatory regulatory inspection dates. Let's see how to design a relational schema and underwriting evaluation model in Laravel that captures this specialized domain.

## The Engineering Domain Challenge

A single industrial manufacturing plant may contain dozens of pressurized units: high-pressure steam boilers, compressed air receivers, hot water storage tanks, and chemical autoclaves. Underwriting risk is determined by:

1. **Working pressure versus design pressure**: Vessels operating near maximum allowable working pressure (MAWP) carry higher explosion risk.
2. **Inspection and certificate currency**: An uncertified boiler voids statutory compliance and dramatically spikes underwriter liability.
3. **Combined mechanical explosion and business interruption (BI)**: When the primary steam boiler bursts, plant production stops instantly.

Flattening these technical attributes into generic JSON columns prevents automated risk queries, authority limit checks, and inspection expiration alerts.

## Step 1: Design the Boiler and Vessel Schema

Define migrations for the engineering equipment catalog and the specific boiler policy schedule:

`database/migrations/2026_09_28_000001_create_boiler_pressure_vessels_table.php:`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boiler_pressure_vessels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained()->cascadeOnDelete();
            $table->string('vessel_type'); // 'steam_boiler', 'fired_pressure_vessel', 'unfired_receiver'
            $table->string('tag_number')->index();
            $table->string('manufacturer');
            $table->year('year_of_manufacture');
            $table->decimal('capacity_liters', 10, 2);
            $table->decimal('max_working_pressure_bar', 8, 2);
            $table->decimal('normal_operating_pressure_bar', 8, 2);
            $table->decimal('sum_insured_usd', 15, 2);
            $table->date('last_hydrostatic_test_date');
            $table->date('statutory_certificate_expiry');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boiler_pressure_vessels');
    }
};
```

This table enforces strict numeric values for technical pressure metrics and inspection dates, preventing dirty data from leaking into actuarial models.

## Step 2: Implement the Eloquent Domain Model

Enforce technical sanity checks directly on the model:

`app/Models/BoilerPressureVessel.php:`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoilerPressureVessel extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'capacity_liters' => 'decimal:2',
        'max_working_pressure_bar' => 'decimal:2',
        'normal_operating_pressure_bar' => 'decimal:2',
        'sum_insured_usd' => 'decimal:2',
        'last_hydrostatic_test_date' => 'date',
        'statutory_certificate_expiry' => 'date',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function isCertificateExpired(): bool
    {
        return $this->statutory_certificate_expiry->isPast();
    }

    public function pressureMarginPercent(): float
    {
        if ($this->max_working_pressure_bar <= 0) {
            return 0.0;
        }

        $margin = ($this->max_working_pressure_bar - $this->normal_operating_pressure_bar) 
            / $this->max_working_pressure_bar * 100;

        return round($margin, 2);
    }
}
```

The `pressureMarginPercent()` helper calculates the safety buffer between operating pressure and burst design limits. A margin below 10% signals a high-stress mechanical environment.

## Step 3: Validate BPV Underwriting Rules

Create a dedicated underwriting assessment service to check boiler safety before quote issuance:

`app/Services/BoilerRiskAssessmentService.php:`
```php
<?php

namespace App\Services;

use App\Models\BoilerPressureVessel;
use Illuminate\Support\Collection;

class BoilerRiskAssessmentService
{
    /**
     * @param Collection<int, BoilerPressureVessel> $vessels
     * @return array{can_quote: bool, warnings: array<string>}
     */
    public function assessFleet(Collection $vessels): array
    {
        $warnings = [];
        $canQuote = true;

        foreach ($vessels as $vessel) {
            if ($vessel->isCertificateExpired()) {
                $warnings[] = "Vessel {$vessel->tag_number} has expired statutory inspection certificate ({$vessel->statutory_certificate_expiry->toDateString()}).";
                $canQuote = false;
            }

            if ($vessel->normal_operating_pressure_bar >= $vessel->max_working_pressure_bar) {
                $warnings[] = "Vessel {$vessel->tag_number} operates at or above maximum allowable working pressure.";
                $canQuote = false;
            }

            if ($vessel->pressureMarginPercent() < 10.0) {
                $warnings[] = "Vessel {$vessel->tag_number} safety margin is dangerously low ({$vessel->pressureMarginPercent()}%).";
            }
        }

        return [
            'can_quote' => $canQuote,
            'warnings' => $warnings,
        ];
    }
}
```

The assessment returns strict pass/fail status and audit warnings, ensuring underwriters cannot issue binders on machinery that lacks valid government certificates.

## What Can Go Wrong

- **Unit of Measurement Confusion:** Recording some vessels in PSI and others in bar or kilopascals silently invalidates calculations. Standardize on one SI unit (such as `bar`) at the database boundary and provide conversion utilities in the presentation layer.
- **Neglecting Age Depreciation:** High-pressure steam boilers older than 20 years have elevated metallurgical fatigue. Failure to factor vessel age into maximum deductible thresholds exposes insurers to heavy machinery breakdown losses.

## Summary

Generic property structures fail for technical engineering insurance. By isolating pressure vessels into a dedicated schema with explicit pressure ratings, certificate expiry tracking, and domain validation services, you give underwriters actionable risk controls before binding coverage.

## Further Reading

- [Laravel Migrations & Schema Building](https://laravel.com/docs/migrations)
- [ASME Boiler and Pressure Vessel Code (BPVC)](https://www.asme.org/codes-standards/bpvc-standards)
- [Laravel Eloquent Custom Casts](https://laravel.com/docs/eloquent-mutators)

Ready to expand engineering lines? Wire the assessment service into your quote approval pipeline.
