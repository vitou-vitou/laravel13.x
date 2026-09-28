---
title: "How to Generate Boiler Inspection Certificate PDFs with Browsershot"
published: true
description: "Render high-fidelity industrial Boiler and Pressure Vessel inspection certificates and policy schedules using Headless Chromium and Tailwind in Laravel."
tags: "laravel, pdf, browsershot, tailwind, insurance"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/93-boiler-inspection-certificate-pdf-browsershot.md"
---

Boiler and Pressure Vessel certificates serve as official legal documents presented to factory safety regulators, workplace inspectors, and reinsurance auditors. Legacy PDF libraries like DomPDF often break when rendering multi-column technical specification tables, precise engineering borders, and high-resolution compliance badges.

Using Spatie Browsershot with Headless Chromium allows you to build PDF certificate templates using modern Tailwind CSS and Flexbox/Grid, ensuring pixel-perfect output. Let's see how.

## The Problem: DomPDF Limitations with Engineering Schedules

Industrial certificates have demanding layout requirements:

1. **Precision tabular data**: Vessel serials, hydrostatic test dates, and safety valve release thresholds must align across multi-page page breaks.
2. **Watermarks and official stamps**: Government ministry approval stamps and underwriter signatures require exact absolute positioning.
3. **No CSS3 Grid support**: Older PHP PDF generators crash or drop layout formatting when encountering modern Tailwind flex/grid classes.

Browsershot resolves these pain points by executing a real Headless Chrome instance to render the HTML before capturing the print stream.

## Step 1: Design the Blade Template for the Certificate

Create a clean Blade view utilizing Tailwind print utilities:

`resources/views/pdf/boiler-certificate.blade.php:`
```blade
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Boiler & Pressure Vessel Certificate</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm;
    }
  </style>
</head>
<body class="bg-white text-gray-900 font-sans text-xs">
  <!-- Certificate Header -->
  <div class="border-b-2 border-blue-900 pb-4 mb-6 flex justify-between items-center">
    <div>
      <h1 class="text-xl font-bold tracking-wider text-blue-900 uppercase">Certificate of Inspection</h1>
      <p class="text-gray-500">Boiler & Pressure Plant Insurance Underwriting</p>
    </div>
    <div class="text-right">
      <p class="font-semibold">Certificate No: <span class="text-blue-900">{{ $policy->policy_number }}</span></p>
      <p class="text-gray-500">Issue Date: {{ now()->format('d M Y') }}</p>
    </div>
  </div>

  <!-- Insured Plant Information -->
  <div class="mb-6 bg-gray-50 p-4 rounded border border-gray-200">
    <h2 class="font-bold text-gray-700 uppercase mb-2">Insured Premises</h2>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <p><span class="font-semibold">Plant Name:</span> {{ $policy->insured_name }}</p>
        <p><span class="font-semibold">Location:</span> {{ $policy->plant_address }}</p>
      </div>
      <div>
        <p><span class="font-semibold">Period of Insurance:</span> {{ $policy->start_date->format('d/m/Y') }} to {{ $policy->end_date->format('d/m/Y') }}</p>
        <p><span class="font-semibold">Total Fleet Sum Insured:</span> ${{ number_format($policy->total_sum_insured, 2) }}</p>
      </div>
    </div>
  </div>

  <!-- Technical Vessel Schedule -->
  <div class="mb-6">
    <h2 class="font-bold text-gray-700 uppercase mb-2">Inspected Pressure Units Schedule</h2>
    <table class="w-full text-left border-collapse border border-gray-300">
      <thead>
        <tr class="bg-gray-100 text-gray-700 font-semibold border-b border-gray-300">
          <th class="p-2 border-r border-gray-300">Tag / Serial</th>
          <th class="p-2 border-r border-gray-300">Type</th>
          <th class="p-2 border-r border-gray-300">MAWP (Bar)</th>
          <th class="p-2 border-r border-gray-300">Test Date</th>
          <th class="p-2 border-r border-gray-300">Cert Expiry</th>
          <th class="p-2 text-right">Sum Insured</th>
        </tr>
      </thead>
      <tbody>
        @foreach($policy->vessels as $vessel)
          <tr class="border-b border-gray-200">
            <td class="p-2 border-r border-gray-200 font-mono">{{ $vessel->tag_number }}</td>
            <td class="p-2 border-r border-gray-200">{{ ucfirst(str_replace('_', ' ', $vessel->vessel_type)) }}</td>
            <td class="p-2 border-r border-gray-200 text-center">{{ $vessel->max_working_pressure_bar }}</td>
            <td class="p-2 border-r border-gray-200">{{ $vessel->last_hydrostatic_test_date->format('d/m/Y') }}</td>
            <td class="p-2 border-r border-gray-200">{{ $vessel->statutory_certificate_expiry->format('d/m/Y') }}</td>
            <td class="p-2 text-right">${{ number_format($vessel->sum_insured_usd, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- Statutory Warranty Notice -->
  <div class="mt-8 border border-red-200 bg-red-50 p-3 rounded text-red-900 text-[10px]">
    <strong>Statutory Warranty:</strong> This policy remains valid only so long as all insured pressure vessels maintain current certificates of fitness issued by accredited government boiler inspectors. Any pressure increase beyond certified MAWP voids coverage.
  </div>
</body>
</html>
```

The template uses standard Tailwind utility classes for crisp borders and tabular alignment.

## Step 2: Render with Browsershot

Build the PDF generation controller action:

`app/Http/Controllers/BoilerCertificateController.php:`
```php
<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Response;
use Spatie\Browsershot\Browsershot;

class BoilerCertificateController extends Controller
{
    public function show(Policy $policy): Response
    {
        $policy->load('vessels');

        $html = view('pdf.boiler-certificate', compact('policy'))->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Certificate-{$policy->policy_number}.pdf\"",
        ]);
    }
}
```

The `showBackground()` and `waitUntilNetworkIdle()` flags ensure all CSS styling and web fonts render completely before Chrome writes the PDF buffer.

## What Can Go Wrong

- **Missing Chromium Dependencies on Alpine/Ubuntu Servers:** Headless Chromium requires system libraries (like `nss`, `freetype`, `harfbuzz`, and `libxss`). Ensure your production Dockerfile includes `chromium` and sets the binary path via `Browsershot::setChromePath()`.
- **Large Document Timeouts:** Generating 50-page equipment schedules can exceed PHP's default 30-second execution limit. Offload large certificate batches to background queues using Laravel jobs.

## Summary

Headless Chrome via Browsershot guarantees that complex engineering certificates render cleanly with exact tabular alignments, proper CSS backgrounds, and professional typography.

## Further Reading

- [Spatie Browsershot GitHub Repository](https://github.com/spatie/browsershot)
- [Puppeteer Documentation](https://pptr.dev)
- [Tailwind CSS Print Styling](https://tailwindcss.com/docs/hover-focus-and-other-states#print-styles)

Next step: add a "Download Certificate" action to your policy view interface.
