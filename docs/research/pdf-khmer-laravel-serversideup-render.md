# Khmer PDFs with Laravel + Spatie Browsershot on serversideup/php + Render — primary-source map

**Date:** 2026-09-28
**Scope:** Spatie Browsershot v4 (source + docs) → headless Chromium via Puppeteer; `serversideup/php:8.4-fpm-nginx` (Dockerfile source + env spec); Render runtime Docker (docs markdown); Khmer font origins (Google Fonts CSS API, Debian package index); repo evidence (`examples/dynamic-warm-view-1906/Dockerfile`, `examples/dynamic-warm-view-1906/render.yaml`). No secondary write-ups.

**Verdict:** Khmer tofu in PDFs is a missing-font + missing-browser problem, not a Blade problem. Self-host a Khmer font (`lang="km"`, UTF-8, no CDN CSS in PDF HTML), install `chromium` + `fonts-khmeros` as root on the serversideup image (Node 22 from NodeSource, **not** Debian `nodejs`), and run Browsershot with `noSandbox()` + `disable-dev-shm-usage` on Render `runtime: docker` with ≥2 GB RAM.

---

## 1. Spatie Browsershot v4 — what the source owns

Source: `https://github.com/spatie/browsershot` (`src/Browsershot.php`, `bin/browser.cjs`); docs: `https://spatie.be/docs/browsershot/v4/requirements`, `.../v4/usage/creating-pdfs`, `.../v4/miscellaneous-options/disable-sandboxing`.

| Claim | Source |
|---|---|
| Converts page → image/PDF behind the scenes with Puppeteer running headless Chrome | README: "The conversion is done behind the scenes by Puppeteer which runs a headless version of Google Chrome" (`https://github.com/spatie/browsershot`) |
| Requires **Node 22.0 (LTS) or higher** and **Puppeteer v23.0 or higher** | `https://spatie.be/docs/browsershot/v4/requirements` |
| PHP side needs only `spatie/temporary-directory`, `symfony/process`, `ext-json`, `ext-fileinfo`; PHP `^8.2` | `composer.json` at repo root |
| `setChromePath($path)` sets Puppeteer `executablePath` (custom chrome/chromium binary) | `Browsershot.php:146` (`setOption('executablePath', …)`); `bin/browser.cjs:119` passes `executablePath` to launch; docs `.../v4/requirements` § "Custom chrome/chromium executable path" |
| `addChromiumArguments([...])` prefixes each entry with `--` and forwards as launch `args` | `Browsershot.php:646`; `bin/browser.cjs:120` (`args: options.args`); docs `.../v4/requirements` § "Pass custom arguments to Chromium" |
| `noSandbox()` appends exactly `--no-sandbox` to launch args | `Browsershot.php:501-505` + `getOptionArgs()` at `Browsershot.php:1060` |
| `waitUntilNetworkIdle(true)` → `networkidle0`, `(false)` → `networkidle2` | `Browsershot.php:262` |
| PDF: `savePdf('x.pdf')` writes file (throws `CouldNotTakeBrowsershot::chromeOutputEmpty` if missing), `pdf()` returns raw bytes, `base64pdf()` returns base64 | `Browsershot.php:742-775`; docs `.../v4/usage/creating-pdfs` ("Browsershot will save a pdf if the path passed to the save method has a pdf extension") |
| PDF options: `format('A4')` (Letter/Legal/Tabloid/Ledger/A0–A6), `margins($t,$r,$b,$l)` default mm, `showBackground()`, `landscape()`, `paperSize()`, header/footer template fields (title, url, pageNumber, totalPages) | Docs `.../v4/usage/creating-pdfs`; `Browsershot.php:430,491,556` |
| Node/npm discovery overrides: `setNodeBinary`, `setNpmBinary`, `setIncludePath`, `setNodeEnv`, `setNodeModulePath`, `setBinPath` | Docs `.../v4/requirements`; `Browsershot.php:111-141` |
| Test suite additionally needs `pdftotext` (poppler-utils) | README (`spatie/pdf-to-text` requirements) — same binary used below as proof command |

---

## 2. Puppeteer upstream — sandbox, Docker, cache dir

Source: `https://pptr.dev/troubleshooting`, `https://pptr.dev/guides/docker`.

| Claim | Source |
|---|---|
| `--no-sandbox` launch arg is the documented fallback; "Running without a sandbox is strongly discouraged. Consider configuring a sandbox instead." | `https://pptr.dev/troubleshooting` § sandbox errors |
| Since v19 browsers download to `~/.cache/puppeteer`; override with `PUPPETEER_CACHE_DIR` env or `.puppeteerrc.js` `cacheDirectory` | `https://pptr.dev/troubleshooting` § "Could not find expected browser locally" |
| Official Docker image runs browser **in** sandbox mode and requires `SYS_ADMIN` capability; needs an init process (`--init`) so Chromium children are reaped | `https://pptr.dev/guides/docker` |
| On constrained hosts (Heroku buildpack lineage) the documented flags are `--no-sandbox` + `--disable-setuid-sandbox`; CJK rendering needs an extra font buildpack — same class of fix as Khmer here | `https://pptr.dev/troubleshooting` § Heroku / CJK note |
| Debian/Ubuntu Chromium needs the shared-lib set (`libx11-xcb1 libxcomposite1 libasound2… libgbm1 …`) — install via apt when using distro Chromium instead of Puppeteer-managed Chrome | `https://spatie.be/docs/browsershot/v4/requirements` § Forge Ubuntu 24.04 (applies to any Debian base) |

---

## 3. serversideup/php 8.4-fpm-nginx — Dockerfile facts

Source: `https://github.com/serversideup/docker-php` (`src/variations/fpm-nginx/Dockerfile`, `docs/content/docs/8.reference/1.environment-variable-specification.md`); repo: `examples/dynamic-warm-view-1906/Dockerfile`.

| Claim | Source |
|---|---|
| Base OS is Debian **bookworm** (`ARG BASE_OS_VERSION='bookworm'`, `php:8.x-fpm-bookworm`); apt is the package tool | `src/variations/fpm-nginx/Dockerfile:2-4`, nginx-repo stage uses `apt-get update && apt-get install` |
| Image **ends on `USER www-data`** — any `apt-get` must run as root *before* the final user switch | `src/variations/fpm-nginx/Dockerfile:251` (`USER www-data`); repo `examples/dynamic-warm-view-1906/Dockerfile` follows the same `USER root` → install → `USER www-data` order |
| `AUTORUN_ENABLED=true` gates every `AUTORUN_*`; migration = `php artisan migrate --force`, storage-link and `optimize`/`config:cache` have their own keys | `docs/content/docs/8.reference/1.environment-variable-specification.md:32-43`; repo render.yaml sets all three (`AUTORUN_ENABLED`, `AUTORUN_LARAVEL_MIGRATION`, `AUTORUN_LARAVEL_STORAGE_LINK`) |
| File ownership convention is `www-data:www-data` (`chown -R www-data:www-data /var/www`, nginx cache, composer home) | `src/variations/fpm-nginx/Dockerfile:202-249` |

**Node 22 gotcha (verified):** Debian bookworm ships `nodejs` **18.20.4** (`https://packages.debian.org/bookworm/nodejs`) — below Browsershot v4's Node 22 floor. `apt-get install nodejs` is therefore *wrong* on this base; install Node 22 via NodeSource (or `npm i -g puppeteer` against a Node 22 binary) instead.

---

## 4. Khmer fonts — primary origins

| Claim | Source |
|---|---|
| **Noto Sans Khmer** and **Noto Serif Khmer** ship a `khmer` unicode-range subset `U+1780–17FF, U+19E0–19FF` — live on Google Fonts | `https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@400;700&display=swap`, `.../family=Noto+Serif+Khmer...` (both return `@font-face` with the Khmer block); specimens at `https://fonts.google.com/noto/specimen/Noto+Sans+Khmer` |
| **Kantumruy Pro** (common Cambodia UI font) also on Google Fonts with a Khmer subset | `https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;700&display=swap` |
| Debian package name is **`fonts-khmeros`** (5.0-9, "KhmerOS Unicode fonts for the Khmer language of Cambodia") — present in **bookworm** and trixie. There is **no** `fonts-khmeros-core` in Debian | `https://packages.debian.org/bookworm/fonts-khmeros`; name search `https://packages.debian.org/search?keywords=khmer&searchon=names&suite=stable` (5 hits: `fonts-khmeros`, `fonts-khmeros-udeb`, `task-khmer*`) |
| Debian Chromium exists on bookworm (`chromium` 154.x, section web) | `https://packages.debian.org/bookworm/chromium` |

Practical rules (consequence of the above, not separate claims): prefer `@font-face` self-host over Google-Fonts CDN in PDF HTML (one less network wait); always `lang="km"` + `<meta charset="UTF-8">`; never put Tailwind CDN `<script>` in PDF HTML — with `waitUntilNetworkIdle()` (`networkidle0`) a long-lived CDN/polling connection means the wait never settles or the font arrives after print.

---

## 5. Render — runtime docker, checks, memory, disk

Source (markdown endpoints): `https://render.com/docs/docker.md`, `.../web-services.md`, `.../disks.md`, `.../health-checks.md`, `.../blueprint-spec.md`, `.../deploy-php-laravel-docker.md`; repo: `examples/dynamic-warm-view-1906/render.yaml`.

| Claim | Source |
|---|---|
| PHP is **not** a native runtime — "Your project uses a language that Render doesn't support natively, such as PHP … → use Docker" | `https://render.com/docs/docker.md` § "Docker or native runtime?" |
| Laravel-on-Render reference uses `runtime: docker` + env (`APP_KEY`, `DATABASE_URL`, `DB_CONNECTION`) | `https://render.com/docs/deploy-php-laravel-docker.md` |
| Blueprint keys: `runtime: docker`, `dockerfilePath`, `dockerContext`, `healthCheckPath`, `plan`, `generateValue: true` | `https://render.com/docs/blueprint-spec.md:66-135,678-720`; repo `examples/dynamic-warm-view-1906/render.yaml` (full working example: `healthCheckPath: /api/healthz`, `LOG_CHANNEL: stderr`, SQLite env, `AUTORUN_*`) |
| Memory: `free` = 0.1 CPU / **512 MB**; paid scale e.g. `1c-2g` = 1 CPU / 2 GB. Chromium + Khmer shaping OOMs on 512 MB — use ≥2 GB for PDF services | `https://render.com/docs/web-services.md:247-253` |
| Filesystem is **ephemeral** without a paid persistent disk — SQLite, stored PDFs, and `~/.cache/puppeteer` vanish on redeploy/restart | `https://render.com/docs/disks.md` ("By default, Render services have an ephemeral filesystem") |
| Health checks hit web services every few seconds; HTTP path checks are opt-in per service | `https://render.com/docs/health-checks.md` |

No Render-published `/dev/shm` size was found — `disable-dev-shm-usage` is carried as standard container hardening (Docker default 64 MB shm vs Chromium's expectation), not as a Render-quoted limit.

---

## 6. Exact wiring (copy-paste)

`Dockerfile:` (extends repo `examples/dynamic-warm-view-1906/Dockerfile` pattern — root install, then drop to `www-data`)

```dockerfile
FROM serversideup/php:8.4-fpm-nginx
USER root
# NodeSource for Node 22 (bookworm apt nodejs is 18 — too old for Browsershot v4)
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get update && apt-get install -y --no-install-recommends \
        chromium \
        fonts-khmeros \
        fonts-noto-core \
        poppler-utils \
        nodejs \
    && rm -rf /var/lib/apt/lists/* \
    && fc-cache -f \
    && npm install -g puppeteer \
    && mkdir -p /tmp/puppeteer-cache && chown -R www-data:www-data /tmp/puppeteer-cache
USER www-data
```

`app/Http/Controllers/CertificateController.php:` (methods verified §1)

```php
$pdf = Browsershot::html($html)
    ->setChromePath('/usr/bin/chromium')
    ->noSandbox()
    ->addChromiumArguments(['disable-gpu', 'disable-dev-shm-usage'])
    ->format('A4')
    ->margins(10, 10, 10, 10)
    ->showBackground()
    ->waitUntilNetworkIdle()
    ->pdf();
```

`render.yaml:` (keys verified §5; values follow repo `examples/dynamic-warm-view-1906/render.yaml`)

```yaml
services:
  - type: web
    runtime: docker
    dockerfilePath: ./Dockerfile
    dockerContext: .
    plan: 1c-2g
    healthCheckPath: /api/healthz
    envVars:
      - key: APP_KEY
        generateValue: true
      - key: LOG_CHANNEL
        value: stderr
      - key: PUPPETEER_CACHE_DIR
        value: /tmp/puppeteer-cache
      - key: AUTORUN_ENABLED
        value: "true"
      - key: AUTORUN_LARAVEL_MIGRATION
        value: "true"
      - key: AUTORUN_LARAVEL_STORAGE_LINK
        value: "true"
```

Proof (requires `poppler-utils` — Browsershot README requirement):

```bash
fc-list | grep -i -E "khmer|noto"   # fonts visible to Chromium
pdftotext storage/app/cert.pdf - | head -n 5   # Khmer glyphs survived, not tofu
```

---

## 7. What can go wrong

| Symptom | Root cause | Fix (source) |
|---|---|---|
| `□□□□` tofu for Khmer, Latin fine | No Khmer font in image (slim base); dev Mac masked it | Install `fonts-khmeros` (+ `fonts-noto-core`); `fc-cache -f`; verify `fc-list` (§4) |
| `No usable sandbox!` / crash as `www-data` | Chrome sandbox needs privileges the container user lacks | `->noSandbox()` = `--no-sandbox` (§1); Puppeteer: strongly discouraged outside containers (§2) |
| Tab crash / OOM mid-render, small `/dev/shm` | Chromium shared-memory expectation vs Docker default | `disable-dev-shm-usage` in `addChromiumArguments` (§1–2) |
| OOM kill halfway through a large schedule | 512 MB free tier too small for Chromium | `plan: 1c-2g` or higher (§5) |
| `waitUntilNetworkIdle()` hangs or font arrives late | Tailwind CDN / polling connection keeps network busy (`networkidle0` never fires) | Remove CDN `<script>` from PDF HTML; self-host fonts + inline CSS (§1, §4) |
| `node: command not found` or Puppeteer install fails | Debian `nodejs` 18 < v4 floor of Node 22 | NodeSource Node 22 (§3); `setNodeBinary`/`setNpmBinary` overrides exist (§1) |
| Chrome binary not found after deploy | Puppeteer cache under ephemeral `$HOME` wiped on restart | `PUPPETEER_CACHE_DIR=/tmp/puppeteer-cache` (writable; Puppeteer troubleshooting §2); or pin `setChromePath('/usr/bin/chromium')` (§1) |
| PDFs / SQLite rows vanish on redeploy | Render ephemeral filesystem without a disk | Persistent disk or object storage; SQLite documented ephemeral in repo rule + `.../disks.md` (§5) |
| `CouldNotTakeBrowsershot::chromeOutputEmpty` | Target PDF never written (crash/timeout above) | Read the chained Chromium output; `LOG_CHANNEL=stderr` surfaces it in Render logs (repo render.yaml §5) |

---

## Sources

**Browsershot (Spatie, first-party)**
- `https://github.com/spatie/browsershot` (source: `src/Browsershot.php`, `bin/browser.cjs`, `composer.json`)
- `https://spatie.be/docs/browsershot/v4/requirements`
- `https://spatie.be/docs/browsershot/v4/usage/creating-pdfs`
- `https://spatie.be/docs/browsershot/v4/miscellaneous-options/disable-sandboxing`
- `https://spatie.be/docs/browsershot/v4/introduction`

**Puppeteer (upstream, first-party)**
- `https://pptr.dev/troubleshooting`
- `https://pptr.dev/guides/docker`

**serversideup/php (first-party)**
- `https://github.com/serversideup/docker-php` (`src/variations/fpm-nginx/Dockerfile`, `docs/content/docs/8.reference/1.environment-variable-specification.md`)
- `https://serversideup.net/open-source/docker-php/`

**Render (first-party docs)**
- `https://render.com/docs/docker.md`
- `https://render.com/docs/deploy-php-laravel-docker.md`
- `https://render.com/docs/web-services.md`
- `https://render.com/docs/disks.md`
- `https://render.com/docs/health-checks.md`
- `https://render.com/docs/blueprint-spec.md`

**Fonts (primary origins)**
- `https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@400;700&display=swap`
- `https://fonts.googleapis.com/css2?family=Noto+Serif+Khmer:wght@400;700&display=swap`
- `https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;700&display=swap`
- `https://fonts.google.com/noto/specimen/Noto+Sans+Khmer`
- `https://packages.debian.org/bookworm/fonts-khmeros`
- `https://packages.debian.org/bookworm/chromium`
- `https://packages.debian.org/bookworm/nodejs`

**Repo evidence (this workspace)**
- `examples/dynamic-warm-view-1906/Dockerfile` (serversideup `USER root` → `USER www-data` + `AUTORUN_*`)
- `examples/dynamic-warm-view-1906/render.yaml` (`runtime: docker`, `healthCheckPath: /api/healthz`, `LOG_CHANNEL: stderr`, SQLite + `AUTORUN_*`)
- `examples/dashboard-v1/Dockerfile` (counter-pattern: `php:8.4-fpm-alpine` multi-stage, no apt/Chromium — why serversideup/Debian is the PDF image)
- `.scratch/blogs/93-boiler-inspection-certificate-pdf-browsershot.md` (sibling draft; **corrections applied here**: `fonts-khmeros-core` → `fonts-khmeros`; `apt nodejs` → NodeSource 22; flags via `noSandbox()` source-confirmed)

*Disclosure: drafted with AI assistance; every claim traces to the URL or file path beside it.*
