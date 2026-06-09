# Fresh install: clone OCP into ocp/runtime, npm install, apply Windows patches
$ErrorActionPreference = "Stop"
$OcpRoot = $PSScriptRoot
$Runtime = Join-Path $OcpRoot "runtime"
$Repo = "https://github.com/dtzp555-max/ocp.git"

function Require-Command($name, $hint) {
    if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
        Write-Host "[install] MISSING: $name - $hint" -ForegroundColor Red
        exit 1
    }
}

Write-Host ""
Write-Host "=== OCP install (Claude Pro proxy for Cursor) ===" -ForegroundColor Cyan
Write-Host ""

Require-Command "node" "Install Node.js 22.5+ from https://nodejs.org"
Require-Command "git" "Install Git from https://git-scm.com"
Require-Command "claude" "npm install -g @anthropic-ai/claude-code; then claude auth login"

$nodeVer = node --version
Write-Host "[install] node $nodeVer" -ForegroundColor DarkGray

try {
    claude auth status 2>&1 | Out-Null
    Write-Host "[install] claude CLI: authenticated" -ForegroundColor Green
} catch {
    Write-Host "[install] claude not logged in - run: claude auth login" -ForegroundColor Yellow
}

if (-not (Get-Command cloudflared -ErrorAction SilentlyContinue)) {
    Write-Host "[install] WARN: cloudflared not on PATH (needed for Cursor tunnel)" -ForegroundColor Yellow
}

if (Test-Path (Join-Path $Runtime ".git")) {
    Write-Host "[install] runtime/ exists - git pull + npm install" -ForegroundColor Yellow
    Push-Location $Runtime
    git pull --ff-only
    npm install
    Pop-Location
} else {
    if (Test-Path $Runtime) {
        Remove-Item $Runtime -Recurse -Force
    }
    Write-Host "[install] Cloning $Repo -> runtime/" -ForegroundColor Cyan
    git clone $Repo $Runtime
    Push-Location $Runtime
    npm install
    Pop-Location
}

& (Join-Path $OcpRoot "patch-windows.ps1")

Write-Host ""
Write-Host "=== Install complete ===" -ForegroundColor Green
Write-Host "Next:" -ForegroundColor Yellow
Write-Host "  1. Double-click start.bat"
Write-Host "  2. Copy the trycloudflare.com URL from the terminal"
Write-Host "  3. Cursor Settings -> Models -> API Keys"
Write-Host "       OpenAI API Key: ocp-local"
Write-Host "       Override OpenAI Base URL: TUNNEL_URL/v1"
Write-Host "  4. Pick a GPT-5 model in chat - see MODEL-MAP.txt"
Write-Host ""
