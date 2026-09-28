---
title: "How to Render Boiler PDFs with Khmer Fonts in Laravel"
published: true
description: "Fix tofu boxes and broken vowel stacks when you print Boiler inspection certificates with Khmer names in Browsershot and Chromium."
tags: "laravel, pdf, browsershot, khmer"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/93-boiler-inspection-certificate-pdf-browsershot.md"
---

Your Boiler certificate looks perfect in the browser. Then you print it to PDF and the Khmer plant name turns into empty boxes. The English serial table is fine, but វិញ្ញាបនបត្រ renders as tofu.

You get a legal document a factory inspector will reject. Let's see how to fix it with self-hosted Khmer fonts and Browsershot.

## Context

This uses Laravel 11, Spatie Browsershot v4, `serversideup/php:8.4-fpm-nginx`, and Render with `runtime: docker`. You already have a Blade certificate view and a `policies` table with a `vessels` relation. One disclaimer: this covers Khmer shaping in Chromium only, not DomPDF.

## Why Khmer Breaks in PDFs

Khmer is a complex script. Vowels sit above, below, and around consonants, and subscript consonants stack. A renderer needs a shaping engine plus a real Khmer font to place them.

Two things go wrong in practice. Your local Mac has Noto Sans Khmer installed, so dev looks fine. Your slim Docker image has zero Khmer fonts, so Chromium draws a box per glyph. And if you load Tailwind from a CDN inside the PDF HTML, `waitUntilNetworkIdle()` never settles and the Khmer webfont arrives after Chrome already printed.

Self-host the font and inline your CSS. Then the PDF no longer depends on the network.

## Step 1: Self-host the Khmer font

Download two files from Google Fonts and commit them to your app. You need one sans for tables and one serif for the certificate heading. Both shape Khmer correctly:

```text
public/fonts/NotoSansKhmer-Regular.ttf
public/fonts/NotoSerifKhmer-Bold.ttf
```

Declare them with `@font-face` in your print stylesheet. Use a font stack that falls back gracefully when a glyph is missing:

`resources/views/pdf/boiler-certificate.blade.php:`

```blade
<style>
  @font-face {
    font-family: 'Noto Sans Khmer';
    src: url('{{ public_path('fonts/NotoSansKhmer-Regular.ttf') }}') format('truetype');
    /* full certificate styles live below */
  }
  body {
    font-family: 'Noto Sans Khmer', 'Noto Serif Khmer', sans-serif;
  }
</style>
```

Chromium now finds the glyphs on disk. No CDN fetch, no race.

Set the document language and charset so shaping kicks in. This one line matters more than it looks:

`resources/views/pdf/boiler-certificate.blade.php:`

```blade
<html lang="km">
<head>
  <meta charset="UTF-8">
  <!-- Khmer heading renders here -->
</head>
```

With `lang="km"` Chromium enables the right OpenType features for vowel placement.

## Step 2: Render with Browsershot without the CDN trap

Drop the `<script src="https://cdn.tailwindcss.com">` tag from your PDF view. That tag is fine for screens and poison for print. Replace it with compiled CSS or plain `<style>` blocks.

Render from local HTML so the font paths resolve:

`app/Http/Controllers/BoilerCertificateController.php:`

```php
$html = view('pdf.boiler-certificate', compact('policy'))->render();

$pdf = Browsershot::html($html)
    ->format('A4')
    ->showBackground()
    ->waitUntilNetworkIdle()
    ->pdf();
```

You get identical output on Mac and Docker because nothing is fetched over HTTP.

Your Khmer heading and English table can now live side by side:

`resources/views/pdf/boiler-certificate.blade.php:`

```blade
<h1 class="cert-title">វិញ្ញាបនបត្រ ត្រួតពិនិត្យឡចំហាយ</h1>
<p class="cert-sub">Certificate of Inspection — Boiler & Pressure Plant</p>
<table>
  <!-- vessel Tag / Serial, MAWP (Bar), Test Date, Sum Insured rows -->
</table>
```

The Khmer line shapes correctly and the vessel serials stay in monospace for easy scanning.

## Step 3: Ship Chromium and Khmer fonts on serversideup + Render

Your local Mac hides the missing-font bug. The serversideup image does too — it ships no Chromium and no Khmer fonts. Install both as root, then drop back to `www-data`:

`Dockerfile:`

```dockerfile
FROM serversideup/php:8.4-fpm-nginx
USER root
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get update && apt-get install -y --no-install-recommends \
        chromium fonts-khmeros fonts-noto-core poppler-utils nodejs \
    && rm -rf /var/lib/apt/lists/* \
    && fc-cache -f
# app copy and Puppeteer install live below
USER www-data
```

Chromium now finds Khmer glyphs via `fc-cache` on every deploy. Bookworm apt `nodejs` is v18, below the Browsershot v4 Node 22 floor, so NodeSource 22 is required.

Tell Browsershot where Chromium lives and give it container-safe flags. Without `no-sandbox` the render dies as `www-data`, and without `disable-dev-shm-usage` it OOMs on small `/dev/shm`:

`app/Http/Controllers/BoilerCertificateController.php:`

```php
$pdf = Browsershot::html($html)
    ->setChromePath('/usr/bin/chromium')
    ->noSandbox()
    ->addChromiumArguments(['disable-gpu', 'disable-dev-shm-usage'])
    ->format('A4')
    ->showBackground()
    ->waitUntilNetworkIdle()
    ->pdf();
```

You get the same PDF locally and on Render because the binary path and flags are explicit.

Use Docker runtime on Render, not native PHP. Native has no `apt-get`, so Chromium can never install. Point at your Dockerfile and keep the health check your blueprint already uses:

`render.yaml:`

```yaml
services:
  - type: web
    runtime: docker
    dockerfilePath: ./Dockerfile
    healthCheckPath: /api/healthz
    # plan, env, and autorun keys live below
```

Pick `1c-2g` or higher for this service. Chromium OOMs on the 512MB free tier halfway through a 50-vessel schedule. Keep `LOG_CHANNEL: stderr` so render logs show the Chromium crash instead of swallowing it.

## Proof it worked

Don't eyeball the PDF. Check the font is visible to Chromium, then extract the text:

```bash
fc-list | grep -i -E "khmer|noto"
pdftotext storage/app/cert-0198.pdf - | head -n 5
```

You want output like this:

```text
វិញ្ញាបនបត្រ ត្រួតពិនិត្យឡចំហាយ
Certificate No: BPV-2026-0198
រោងចក្រ៖ Phnom Penh Boiler Plant
```

If you see `□□□□` here, Chromium still lacks the font. If you see Khmer, your inspector copy is safe.

## What can go wrong

Ephemeral disks bite you on Render. SQLite and locally stored PDFs vanish on redeploy, and the Puppeteer cache rewrites each boot. Store finished certificates on S3 or a persistent disk, and set `PUPPETEER_CACHE_DIR: /tmp/puppeteer-cache` so Chromium starts writable.

Large fleet schedules hit a second wall. A 50-vessel certificate can exceed the 30-second PHP limit while Chromium lays out Khmer stacks. Push that job to a queue and store the PDF on disk instead of rendering inline.

Also watch your file size. Two Khmer TTFs add about 400KB. That is fine for a legal certificate and heavy for a one-page email attachment. Subset the font if you ever print thousands per day.

## Summary

Khmer tofu in Boiler PDFs is a missing-font problem, not a Blade problem. Self-host Noto Sans Khmer, set `lang="km"`, install Chromium plus `fonts-khmeros` on your serversideup image, and run it on Render with Docker runtime and 2GB RAM.

## Further reading

- [Spatie Browsershot docs](https://spatie.be/docs/browsershot/v4/introduction)
- [Noto Sans Khmer on Google Fonts](https://fonts.google.com/noto/specimen/Noto+Sans+Khmer)
- [Serversideup PHP Docker images](https://serversideup.net/open-source/docker-php/)
- [Render Docker runtime](https://docs.render.com/docker)

Grab the two TTFs, deploy the serversideup image to Render, and run `pdftotext` before you send it to the inspector.

*Disclosure: drafted with AI assistance, tested against Browsershot v4, serversideup/php 8.4, and Chromium on Render.*
