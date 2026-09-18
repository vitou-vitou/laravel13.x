param(
    [string]$TargetRepo = (Get-Location).Path,
    [switch]$UserGlobal
)

$src = $PSScriptRoot

if ($UserGlobal) {
    $dst = "$env:USERPROFILE\.cursor\skills\git-push"
} else {
    $dst = Join-Path $TargetRepo ".cursor\skills\git-push"
}

Write-Host "Installing git-push skill..."
Write-Host "  Source: $src"
Write-Host "  Target: $dst"

$srcFull = (Resolve-Path $src).Path
$dstFull = $null
try { $dstFull = (Resolve-Path $dst -ErrorAction Stop).Path } catch {}

if ($dstFull -and ($srcFull -eq $dstFull)) {
    Write-Host "  (Already in place, skipping copy)"
} else {
    New-Item -ItemType Directory -Force -Path $dst | Out-Null
    Copy-Item -Recurse -Force "$src\*" $dst
}

$scriptsPath = Join-Path $dst "scripts"
$required = @("pre-commit-bundle.ps1")
$missing = @()
foreach ($script in $required) {
    $path = Join-Path $scriptsPath $script
    if (-not (Test-Path $path)) { $missing += $script }
}
if ($missing.Count -gt 0) {
    Write-Host "ERROR: Missing scripts: $($missing -join ', ')" -ForegroundColor Red
    exit 1
}

Write-Host "`nVerifying pre-commit-bundle.ps1..."
$json = & powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $scriptsPath "pre-commit-bundle.ps1") -RepoPath $TargetRepo -Force 2>&1
Write-Host "  Bundle output: $json"
Write-Host "`nInstalled to: $dst" -ForegroundColor Green
Write-Host "Try: /git-push"
