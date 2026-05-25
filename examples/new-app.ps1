#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Scaffold a new Laravel example app from swift-lens-4829 template.

.DESCRIPTION
    Clones swift-lens-4829, sets correct .env defaults (SESSION_DRIVER=file,
    CACHE_STORE=file), removes leftover test artifacts, and starts dev server.

.PARAMETER Name
    App folder name. If omitted, generates adjective-noun-NNNN.

.PARAMETER Port
    Dev server port. Default: auto-pick from 9100+.

.PARAMETER AppName
    Human-readable APP_NAME for .env. Defaults to title-cased Name.

.EXAMPLE
    .\new-app.ps1
    .\new-app.ps1 -Name bright-map-3301 -Port 9200 -AppName "Bright Map"
    .\new-app.ps1 -Sleep          # sleep PC after successful start
#>
param(
    [string]$Name     = "",
    [int]   $Port     = 0,
    [string]$AppName  = "",
    [switch]$Sleep
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

# ── helpers ──────────────────────────────────────────────────────────────────

function Get-RandomName {
    $adjectives = @(
        "swift","bright","calm","crisp","bold","keen","clear","sharp","clean","fresh",
        "quick","light","deep","wide","cool","warm","soft","hard","safe","open"
    )
    $nouns = @(
        "lens","map","hub","grid","flow","deck","view","base","core","link",
        "node","ring","path","edge","gate","port","pulse","scope","wave","trace"
    )
    $adj  = $adjectives[(Get-Random -Maximum $adjectives.Count)]
    $noun = $nouns[(Get-Random -Maximum $nouns.Count)]
    $num  = Get-Random -Minimum 1000 -Maximum 9999
    return "$adj-$noun-$num"
}

function Find-FreePort([int]$start = 9100) {
    for ($p = $start; $p -lt ($start + 100); $p++) {
        $conn = [System.Net.Sockets.TcpClient]::new()
        try {
            $conn.Connect("127.0.0.1", $p)
            $conn.Close()
            # port in use — try next
        } catch {
            return $p   # connection refused = port free
        }
    }
    throw "No free port found in range $start..$($start+99)"
}

# ── resolve params ────────────────────────────────────────────────────────────

if (-not $Name) { $Name = Get-RandomName }

if ($Port -eq 0) { $Port = Find-FreePort 9100 }

if (-not $AppName) {
    # title-case: bright-map-3301 → Bright Map 3301
    $AppName = ($Name -split "-" | ForEach-Object {
        if ($_ -match "^\d+$") { $_ } else { (Get-Culture).TextInfo.ToTitleCase($_) }
    }) -join " "
}

$template = Join-Path $PSScriptRoot "swift-lens-4829"
$dest     = Join-Path $PSScriptRoot $Name

Write-Host ""
Write-Host "  name  : $Name"      -ForegroundColor Cyan
Write-Host "  label : $AppName"   -ForegroundColor Cyan
Write-Host "  port  : $Port"      -ForegroundColor Cyan
Write-Host "  dest  : $dest"      -ForegroundColor Cyan
Write-Host ""

if (Test-Path $dest) {
    Write-Error "Directory already exists: $dest"
    exit 1
}

# ── copy template ─────────────────────────────────────────────────────────────

Write-Host "[1/6] Copying template..." -ForegroundColor Yellow

$exclude = @("node_modules","vendor",".git","database\database.sqlite",
             "storage\logs\*.log","serve*.log","*.phpunit.result.cache")

# robocopy: copy all, skip excluded dirs/files
robocopy $template $dest /E /XD "node_modules" "vendor" ".git" /XF "*.sqlite" "*.log" ".phpunit.result.cache" /NFL /NDL /NJH /NJS | Out-Null

# ── .env setup ────────────────────────────────────────────────────────────────

Write-Host "[2/6] Writing .env..." -ForegroundColor Yellow

$key = & php -r "echo 'base64:'.base64_encode(random_bytes(32));"

$env_content = @"
APP_NAME="$AppName"
APP_ENV=local
APP_KEY=$key
APP_DEBUG=true
APP_URL=http://127.0.0.1:$Port

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=file

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="`${APP_NAME}"

VITE_APP_NAME="`${APP_NAME}"
"@

Set-Content -Path (Join-Path $dest ".env") -Value $env_content -Encoding utf8

# ── install deps ──────────────────────────────────────────────────────────────

Write-Host "[3/6] Installing PHP deps (composer install)..." -ForegroundColor Yellow
Push-Location $dest
composer install --no-interaction --quiet
Pop-Location

Write-Host "[4/6] Installing JS deps (npm install + build)..." -ForegroundColor Yellow
Push-Location $dest
npm install --silent 2>&1 | Out-Null
npm run build --silent 2>&1 | Out-Null
Pop-Location

# ── clean up template artifacts ───────────────────────────────────────────────

Write-Host "[5/6] Cleaning up..." -ForegroundColor Yellow

# Remove leftover test that hits / without a seeded DB (causes 500 in new apps)
$exampleTest = Join-Path $dest "tests\Feature\ExampleTest.php"
if (Test-Path $exampleTest) { Remove-Item $exampleTest -Force }

# Fresh SQLite DB
$dbPath = Join-Path $dest "database\database.sqlite"
if (-not (Test-Path $dbPath)) { New-Item -ItemType File $dbPath | Out-Null }

Push-Location $dest
php artisan config:clear --quiet
php artisan migrate --force --quiet
php artisan db:seed --quiet
Pop-Location

# Router that passes static files through (fixes Vite CSS/JS assets)
$routerPath = Join-Path $dest "public\router.php"
Set-Content -Path $routerPath -Encoding utf8 -Value @'
<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
if ($uri !== '/' && file_exists(__DIR__ . $uri)) { return false; }
require_once __DIR__ . '/index.php';
'@

# ── start server ──────────────────────────────────────────────────────────────

Write-Host "[6/6] Starting dev server..." -ForegroundColor Yellow

$logOut = Join-Path $dest "serve.log"

$proc = Start-Process php `
    -ArgumentList "-S","127.0.0.1:$Port","-t","$dest\public","$dest\public\router.php" `
    -WindowStyle Hidden `
    -PassThru

Start-Sleep -Seconds 2

$status = try {
    $r = Invoke-WebRequest "http://127.0.0.1:$Port" -UseBasicParsing -TimeoutSec 5
    "HTTP $($r.StatusCode)"
} catch { "ERROR: $_" }

Write-Host ""
if ($status -like "HTTP 2*") {
    Write-Host "  App ready at http://127.0.0.1:$Port" -ForegroundColor Green
    Write-Host "  Server PID: $($proc.Id)"
    Write-Host "  Logs: $logOut"
    Write-Host ""
    Write-Host "  Next steps:"
    Write-Host "    cd examples\$Name"
    Write-Host "    # edit app/Http/Controllers/, resources/views/, routes/web.php"
    Write-Host "    # stop server: Stop-Process -Id $($proc.Id)"
} else {
    Write-Host "  WARNING: server check returned: $status" -ForegroundColor Red
    Write-Host "  Check $logOut for details"
}
Write-Host ""

if ($Sleep) {
    if ($status -like "HTTP 2*") {
        Write-Host "  Sleeping PC in 10 seconds... (Ctrl+C to cancel)" -ForegroundColor DarkGray
        Start-Sleep -Seconds 10
        rundll32.exe powrprof.dll,SetSuspendState 0,1,0
    } else {
        Write-Host "  Skipping sleep — app did not start cleanly." -ForegroundColor Yellow
    }
}
