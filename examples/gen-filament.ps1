#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Generate Filament v3 files from a YAML definition into a scaffolded app.

.DESCRIPTION
    Reads a YAML definition file and generates:
      - Migrations, Models, Filament Resources, Pages, RelationManagers
      - StatsOverviewWidget
      - Seeder stub

    Then optionally runs migrate:fresh --seed and npm run build.

.PARAMETER AppDir
    Target app directory (must already be scaffolded with new-app.ps1).

.PARAMETER Definition
    Path to YAML definition file.

.PARAMETER Build
    If set, runs migrate:fresh --seed + npm run build after generation.

.EXAMPLE
    .\gen-filament.ps1 -AppDir jira-clone -Definition definitions\jira.yaml
    .\gen-filament.ps1 -AppDir jira-clone -Definition definitions\jira.yaml -Build
#>
param(
    [Parameter(Mandatory)][string]$AppDir,
    [Parameter(Mandatory)][string]$Definition,
    [switch]$Build
)

$ErrorActionPreference = "Stop"

$appPath  = Join-Path $PSScriptRoot $AppDir
$yamlPath = Join-Path $PSScriptRoot $Definition
$genScript = Join-Path $PSScriptRoot "gen-filament.php"

if (-not (Test-Path $appPath))   { Write-Error "App dir not found: $appPath"; exit 1 }
if (-not (Test-Path $yamlPath))  { Write-Error "YAML not found: $yamlPath";   exit 1 }
if (-not (Test-Path $genScript)) { Write-Error "gen-filament.php not found";  exit 1 }

Write-Host ""
Write-Host "  app  : $AppDir"        -ForegroundColor Cyan
Write-Host "  yaml : $Definition"    -ForegroundColor Cyan
Write-Host ""

# Run generator
php $genScript $appPath $yamlPath

if ($LASTEXITCODE -ne 0) {
    Write-Host "  Generator failed." -ForegroundColor Red
    exit 1
}

if ($Build) {
    Write-Host ""
    Write-Host "  Running migrate:fresh --seed..." -ForegroundColor Yellow
    Push-Location $appPath
    php artisan migrate:fresh --seed --quiet
    Pop-Location

    Write-Host "  Running npm run build..." -ForegroundColor Yellow
    Push-Location $appPath
    npm run build --silent 2>&1 | Out-Null
    Pop-Location

    Write-Host "  Build complete." -ForegroundColor Green
}

Write-Host ""
Write-Host "  Next:" -ForegroundColor Yellow
Write-Host "    1. Edit database/seeders/*Seeder.php — add real data"
Write-Host "    2. cd $AppDir && php artisan make:filament-user"
Write-Host "    3. Visit http://127.0.0.1:<port>/admin"
Write-Host ""
