# Apply Windows + Cursor patches to ocp/runtime (idempotent)
$ErrorActionPreference = "Stop"
$OcpRoot = $PSScriptRoot
$Runtime = Join-Path $OcpRoot "runtime"
$Server = Join-Path $Runtime "server.mjs"
$Models = Join-Path $Runtime "models.json"
$AliasesFile = Join-Path $OcpRoot "patches\cursor-legacy-aliases.json"

if (-not (Test-Path $Server)) {
    Write-Host "[patch] runtime not found. Run install.ps1 first." -ForegroundColor Red
    exit 1
}

$serverText = Get-Content $Server -Raw
$changed = $false

$spawnOld = 'const proc = spawn(CLAUDE, cliArgs, { env, stdio: ["pipe", "pipe", "pipe"] });'
$spawnNew = 'const proc = spawn(CLAUDE, cliArgs, { env, stdio: ["pipe", "pipe", "pipe"], shell: process.platform === "win32" });'

if ($serverText.Contains('shell: process.platform === "win32"')) {
    Write-Host "[patch] spawn: already patched" -ForegroundColor DarkGray
} elseif ($serverText.Contains($spawnOld)) {
    $serverText = $serverText.Replace($spawnOld, $spawnNew)
    $changed = $true
    Write-Host "[patch] spawn: added shell for Windows" -ForegroundColor Green
} else {
    Write-Host "[patch] WARN: spawn line not found - check server.mjs manually" -ForegroundColor Yellow
}

$authOld = 'execFileSync(CLAUDE, ["auth", "status"], { encoding: "utf8", timeout: 10000, env });'
$authNew = 'execFileSync(CLAUDE, ["auth", "status"], { encoding: "utf8", timeout: 10000, env, shell: process.platform === "win32" });'

if ($serverText.Contains('shell: process.platform === "win32"') -and $serverText.Contains('auth", "status"')) {
    if ($serverText.Contains($authOld)) {
        $serverText = $serverText.Replace($authOld, $authNew)
        $changed = $true
        Write-Host "[patch] auth: added shell for Windows" -ForegroundColor Green
    } else {
        Write-Host "[patch] auth: already patched or format changed" -ForegroundColor DarkGray
    }
}

if ($serverText.Contains('WIN_MAX_SYSPROMPT')) {
    Write-Host "[patch] buildCliArgs: already patched" -ForegroundColor DarkGray
} else {
    $needle = 'function buildCliArgs(cliModel, systemPrompt) {'
    $insert = @'
function buildCliArgs(cliModel, systemPrompt) {
  const WIN_MAX_SYSPROMPT = 4000;
  const effectivePrompt = (process.platform === "win32" && systemPrompt.length > WIN_MAX_SYSPROMPT)
    ? systemPrompt.slice(0, WIN_MAX_SYSPROMPT)
    : systemPrompt;

'@
    if ($serverText.Contains($needle)) {
        $serverText = $serverText.Replace($needle, $insert)
        $serverText = $serverText.Replace('"--system-prompt", systemPrompt,', '"--system-prompt", effectivePrompt,')
        $changed = $true
        Write-Host "[patch] buildCliArgs: Windows prompt length limit" -ForegroundColor Green
    } else {
        Write-Host "[patch] WARN: buildCliArgs block not found" -ForegroundColor Yellow
    }
}

if ($changed) {
    [System.IO.File]::WriteAllText($Server, $serverText)
}

if (-not (Test-Path $Models)) {
    Write-Host "[patch] WARN: models.json not found" -ForegroundColor Yellow
    exit 0
}

$modelsObj = Get-Content $Models -Raw | ConvertFrom-Json
if (-not $modelsObj.legacyAliases) {
    $modelsObj | Add-Member -NotePropertyName legacyAliases -NotePropertyValue ([ordered]@{}) -Force
}
$aliases = Get-Content $AliasesFile -Raw | ConvertFrom-Json
$aliasChanged = $false
foreach ($prop in $aliases.PSObject.Properties) {
    if (-not $modelsObj.legacyAliases.PSObject.Properties[$prop.Name]) {
        $modelsObj.legacyAliases | Add-Member -NotePropertyName $prop.Name -NotePropertyValue $prop.Value
        $aliasChanged = $true
        Write-Host "[patch] models: $($prop.Name) -> $($prop.Value)" -ForegroundColor Green
    }
}
if ($aliasChanged) {
    $modelsObj | ConvertTo-Json -Depth 20 | Set-Content $Models -Encoding utf8
} else {
    Write-Host "[patch] models: Cursor aliases already present" -ForegroundColor DarkGray
}

Write-Host "[patch] Done." -ForegroundColor Cyan
