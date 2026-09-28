---
title: "How to Ingest Industrial Boiler CSV Fleets with Chunked Validation in Laravel"
published: true
description: "Process multi-megabyte equipment schedules containing hundreds of pressure vessels using Laravel chunked CSV streaming, row-level validation, and atomic batch inserts."
tags: "laravel, csv, performance, queues, streams"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/97-boiler-csv-fleet-ingestion-laravel.md"
---

Commercial brokers rarely type fifty pressure vessels into a web form one by one. Instead, they upload an Excel or CSV schedule exported from the factory's plant asset management system. Parsing these files using standard in-memory arrays causes memory spikes, timeouts, and unhelpful fatal errors when invalid row data appears.

Building a chunked CSV ingestion pipeline in Laravel allows underwriters to upload multi-megabyte plant asset schedules with row-level validation, custom error reporting, and fast database batch insertions. Let's see how.

## The Problem: Memory Exhaustion on Large Asset Fleets

A straightforward CSV implementation reads the entire uploaded file into memory using `file()` or `fgetcsv()` in a controller:

```php
$rows = array_map('str_getcsv', file($request->file('schedule')->path()));
```

For large chemical or petroleum installations with hundreds of vessels:
1. PHP hits memory limits (`Allowed memory size of X bytes exhausted`).
2. If row 142 contains an invalid date, the entire request fails without telling the user which rows were clean.
3. Database inserts execute as single queries in a loop, resulting in hundreds of sequential round-trips.

We can solve this by streaming the file, validating in batches, and performing bulk inserts.

## Step 1: Create the Row Validation Rule

Define an explicit validator for an individual boiler schedule row:

`app/Rules/BoilerRowValidator.php:`
```php
<?php

namespace App\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BoilerRowValidator
{
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public static function validate(array $row, int $rowNumber): array
    {
        $validator = Validator::make($row, [
            'tag_number' => ['required', 'string', 'max:50'],
            'vessel_type' => ['required', 'in:steam_boiler,fired_pressure_vessel,unfired_receiver'],
            'max_working_pressure_bar' => ['required', 'numeric', 'min:0.1', 'max:500'],
            'normal_operating_pressure_bar' => ['required', 'numeric', 'min:0.1'],
            'sum_insured_usd' => ['required', 'numeric', 'min:1000'],
            'statutory_certificate_expiry' => ['required', 'date_format:Y-m-d'],
            'last_hydrostatic_test_date' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator, null, "Row #{$rowNumber}: " . implode(', ', $validator->errors()->all()));
        }

        return $validator->validated();
    }
}
```

The validator returns validated attributes or throws an exception pointing directly to the specific row number.

## Step 2: Implement the Stream Ingestion Service

Build a streaming importer that reads the file handle without loading it entirely into memory:

`app/Services/BoilerFleetImportService.php:`
```php
<?php

namespace App\Services;

use App\Models\BoilerPressureVessel;
use App\Models\Policy;
use App\Rules\BoilerRowValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BoilerFleetImportService
{
    public function import(Policy $policy, UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \RuntimeException('Failed to open uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new \RuntimeException('CSV schedule is empty.');
        }

        // Normalize header keys
        $header = array_map(fn($col) => strtolower(trim(str_replace(' ', '_', $col))), $header);

        $rowNumber = 1;
        $batch = [];
        $errors = [];
        $insertedCount = 0;

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $rowNumber++;
                
                // Skip empty lines
                if (count(array_filter($data)) === 0) {
                    continue;
                }

                $mappedRow = array_combine($header, $data);

                try {
                    $valid = BoilerRowValidator::validate($mappedRow, $rowNumber);
                    $batch[] = array_merge($valid, [
                        'policy_id' => $policy->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (ValidationException $e) {
                    $errors[] = $e->getMessage();
                }

                // Flush in chunks of 50
                if (count($batch) >= 50) {
                    BoilerPressureVessel::insert($batch);
                    $insertedCount += count($batch);
                    $batch = [];
                }
            }

            if (count($batch) > 0) {
                BoilerPressureVessel::insert($batch);
                $insertedCount += count($batch);
            }

            if (count($errors) > 0) {
                DB::rollBack();
                fclose($handle);
                return [
                    'success' => false,
                    'errors' => $errors,
                ];
            }

            DB::commit();
            fclose($handle);

            return [
                'success' => true,
                'imported_count' => $insertedCount,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }
    }
}
```

The streaming reader processes the file line-by-line with constant memory usage ($O(1)$) and flushes records in 50-row database batches.

## What Can Go Wrong

- **CSV Header Variations from Different Plant Systems:** Client spreadsheets often label columns differently (`Tag#`, `Tag Number`, `Vessel ID`). Providing an initial column mapping step in the frontend avoids unnecessary ingestion failures.
- **Excel Windows BOM (Byte Order Mark) Corruption:** UTF-8 CSVs exported from Microsoft Excel often prepend a three-byte BOM (`\xEF\xBB\xBF`) to the first header column. Strip BOM characters before normalizing column headers.

## Summary

Streaming CSV uploads and chunking database inserts enables underwriters to ingest massive industrial machinery schedules smoothly without exhausting PHP memory or losing row-specific validation context.

## Further Reading

- [PHP `fgetcsv` Stream Functions](https://www.php.net/manual/en/function.fgetcsv.php)
- [Laravel Validation Rules](https://laravel.com/docs/validation)
- [Optimizing Mass Database Inserts in Eloquent](https://laravel.com/docs/eloquent#inserting-and-updating-models)

Next step: build an upload modal in your Vue 3 equipment schedule interface.
