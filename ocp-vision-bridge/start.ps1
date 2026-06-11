# Start OCP Vision Bridge (requires OCP on :3456 for text-only passthrough)
$ErrorActionPreference = "Stop"
$Root = $PSScriptRoot
$Port = if ($env:VISION_BRIDGE_PORT) { $env:VISION_BRIDGE_PORT } else { 3457 }
$OcpPort = 3456
$PidFile = Join-Path $Root ".vision-bridge.pid"
$Tag = "vision-bridge"

try {
    Invoke-RestMethod -Uri "http://127.0.0.1:$OcpPort/health" -TimeoutSec 3 | Out-Null
    Write-Host "$Tag OCP upstream OK on :$OcpPort" -ForegroundColor Green
} catch {
    Write-Host "$Tag OCP not running on :$OcpPort - start ocp\start.bat first" -ForegroundColor Red
    exit 1
}

if (Test-Path $PidFile) {
    $oldPid = Get-Content $PidFile -ErrorAction SilentlyContinue
    if ($oldPid) { Stop-Process -Id $oldPid -Force -ErrorAction SilentlyContinue }
}

$existing = netstat -ano | Select-String ":$Port.*LISTEN"
if ($existing) {
    $procId = ($existing.ToString() -split '\s+')[-1]
    Stop-Process -Id $procId -Force -ErrorAction SilentlyContinue
    Start-Sleep 1
}

Write-Host "$Tag Starting on http://127.0.0.1:$Port ..." -ForegroundColor Cyan
$proc = Start-Process -FilePath "node" -ArgumentList "server.mjs" -WorkingDirectory $Root -PassThru -WindowStyle Hidden
$proc.Id | Set-Content $PidFile
Start-Sleep 2

try {
    Invoke-RestMethod -Uri "http://127.0.0.1:$Port/health" -TimeoutSec 5 | Out-Null
    Write-Host "$Tag Running (pid $($proc.Id))" -ForegroundColor Green
    Write-Host "$Tag Cursor Base URL: http://127.0.0.1:$Port/v1" -ForegroundColor Green
    Write-Host "$Tag cloudflared: tunnel port $Port (not $OcpPort)" -ForegroundColor Yellow
} catch {
    Write-Host "$Tag Failed to start" -ForegroundColor Red
    exit 1
}
