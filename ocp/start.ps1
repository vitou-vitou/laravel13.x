# Start OCP proxy + Cloudflare tunnel (Cursor needs the public URL)
$ErrorActionPreference = "Stop"
$OcpRoot = $PSScriptRoot
$Runtime = Join-Path $OcpRoot "runtime"
$Port = 3456

if (-not (Test-Path (Join-Path $Runtime "server.mjs"))) {
    Write-Host "[OCP] runtime/ not installed. Run install.bat first." -ForegroundColor Red
    exit 1
}

try {
    $claudePath = (Get-Command claude -ErrorAction Stop).Source
    $env:CLAUDE_BIN = $claudePath
    Write-Host "[OCP] CLAUDE_BIN=$claudePath" -ForegroundColor DarkGray
} catch {
    Write-Host "[OCP] claude CLI not found. Run install.bat after: claude auth login" -ForegroundColor Red
    exit 1
}

# Kill existing listener on port
$existing = netstat -ano | Select-String ":$Port.*LISTEN"
if ($existing) {
    $procId = ($existing.ToString() -split '\s+')[-1]
    Stop-Process -Id $procId -Force -ErrorAction SilentlyContinue
    Write-Host "[OCP] Stopped previous process on port $Port" -ForegroundColor Yellow
    Start-Sleep 2
}

Write-Host "[OCP] Starting proxy..." -ForegroundColor Cyan
$ocpJob = Start-Job -ScriptBlock {
    param($RuntimeDir, $ClaudeBin)
    $env:CLAUDE_BIN = $ClaudeBin
    Set-Location $RuntimeDir
    node server.mjs 2>&1
} -ArgumentList $Runtime, $env:CLAUDE_BIN

Start-Sleep 3

try {
    $models = Invoke-RestMethod -Uri "http://127.0.0.1:$Port/v1/models" -TimeoutSec 8
    $ids = ($models.data | ForEach-Object { $_.id }) -join ', '
    Write-Host "[OCP] Running on http://127.0.0.1:$Port" -ForegroundColor Green
    Write-Host "[OCP] Models: $ids" -ForegroundColor Green
} catch {
    Write-Host "[OCP] Failed to start. Job output:" -ForegroundColor Red
    Receive-Job $ocpJob
    exit 1
}

if (-not (Get-Command cloudflared -ErrorAction SilentlyContinue)) {
    Write-Host ""
    Write-Host "[OCP] cloudflared not found - proxy is local-only." -ForegroundColor Yellow
    Write-Host "      Cursor on this PC may use: http://127.0.0.1:$Port/v1" -ForegroundColor Yellow
    Write-Host "      For remote Cursor, install cloudflared and re-run start.bat" -ForegroundColor Yellow
    exit 0
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "  Copy tunnel URL below + append /v1" -ForegroundColor Yellow
Write-Host "  Cursor -> Models -> Override OpenAI Base URL" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

cloudflared tunnel --url "http://127.0.0.1:$Port"
