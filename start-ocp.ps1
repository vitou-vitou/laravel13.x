# OCP Startup Script for Windows
# Starts OCP proxy + Cloudflare tunnel, outputs tunnel URL for Cursor

$env:CLAUDE_BIN = "C:\Users\vitou\.vite-plus\bin\claude.cmd"

# Kill existing OCP if running
$existing = netstat -ano | Select-String ":3456.*LISTEN"
if ($existing) {
    $procId = ($existing -split '\s+')[-1]
    Stop-Process -Id $procId -Force -ErrorAction SilentlyContinue
    Write-Host "[OCP] Killed existing process on port 3456" -ForegroundColor Yellow
    Start-Sleep 2
}

# Start OCP server in background
Write-Host "[OCP] Starting proxy server..." -ForegroundColor Cyan
$ocpJob = Start-Job -ScriptBlock {
    $env:CLAUDE_BIN = "C:\Users\vitou\.vite-plus\bin\claude.cmd"
    Set-Location "C:\Users\vitou\ocp"
    node server.mjs 2>&1
}

Start-Sleep 3

# Verify OCP is running
try {
    $models = Invoke-RestMethod -Uri "http://127.0.0.1:3456/v1/models" -TimeoutSec 5
    Write-Host "[OCP] Server running! Models: $($models.data.id -join ', ')" -ForegroundColor Green
} catch {
    Write-Host "[OCP] Failed to start. Check logs." -ForegroundColor Red
    Receive-Job $ocpJob
    exit 1
}

# Start Cloudflare tunnel
Write-Host "[OCP] Starting Cloudflare tunnel..." -ForegroundColor Cyan
Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "  WATCH FOR THE TUNNEL URL BELOW" -ForegroundColor Yellow
Write-Host "  Copy it and paste in Cursor Settings:" -ForegroundColor Yellow
Write-Host "  Models > API Keys > Override OpenAI Base URL" -ForegroundColor Yellow
Write-Host "  (add /v1 at the end)" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

# Run tunnel in foreground so user sees the URL
cloudflared tunnel --url http://127.0.0.1:3456
