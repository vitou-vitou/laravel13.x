# Health check for OCP proxy
$ErrorActionPreference = "Continue"
$Port = 3456
$OcpRoot = $PSScriptRoot
$Runtime = Join-Path $OcpRoot "runtime"

Write-Host ""
Write-Host "=== OCP health check ===" -ForegroundColor Cyan

$ok = $true

if (Test-Path (Join-Path $Runtime "server.mjs")) {
    Write-Host "[OK]  runtime/ installed" -ForegroundColor Green
} else {
    Write-Host "[FAIL] runtime/ missing - run install.bat" -ForegroundColor Red
    $ok = $false
}

if (Get-Command claude -ErrorAction SilentlyContinue) {
    Write-Host "[OK]  claude CLI on PATH" -ForegroundColor Green
} else {
    Write-Host "[FAIL] claude CLI missing" -ForegroundColor Red
    $ok = $false
}

if (Get-Command cloudflared -ErrorAction SilentlyContinue) {
    Write-Host "[OK]  cloudflared on PATH" -ForegroundColor Green
} else {
    Write-Host "[WARN] cloudflared missing - tunnel will not work" -ForegroundColor Yellow
}

$listen = netstat -ano | Select-String ":$Port.*LISTEN"
if ($listen) {
    Write-Host "[OK]  listening on 127.0.0.1:$Port" -ForegroundColor Green
    try {
        $models = Invoke-RestMethod -Uri "http://127.0.0.1:$Port/v1/models" -TimeoutSec 5
        Write-Host "[OK]  /v1/models - $($models.data.Count) models" -ForegroundColor Green
        foreach ($m in $models.data) {
            Write-Host "       - $($m.id)" -ForegroundColor DarkGray
        }
    } catch {
        Write-Host "[FAIL] /v1/models unreachable" -ForegroundColor Red
        $ok = $false
    }
} else {
    Write-Host "[WARN] not running - run start.bat" -ForegroundColor Yellow
}

Write-Host ""
if ($ok) { Write-Host "Ready (or start.bat if proxy is down)." -ForegroundColor Green }
else { Write-Host "Fix failures above, then install.bat / start.bat." -ForegroundColor Red }
Write-Host ""
