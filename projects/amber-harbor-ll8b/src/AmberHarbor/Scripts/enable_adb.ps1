# Enable LDPlayer ADB via leidian{N}.config (primary) — no UI clicks required.
param([int]$LdIndex = 0)

$ErrorActionPreference = "Stop"

function Get-LdPlayerHome {
  foreach ($c in @($env:LDPLAYER_HOME, "D:\LDPlayer\LDPlayer9", "C:\Program Files\LDPlayer\LDPlayer9")) {
    if ($c -and (Test-Path (Join-Path $c "ldconsole.exe"))) { return $c }
  }
  throw "LDPlayer not found"
}

$ldHome = Get-LdPlayerHome
$config = Join-Path $ldHome "vms\config\leidian$LdIndex.config"
if (-not (Test-Path $config)) { throw "Config not found: $config" }

$json = Get-Content $config -Raw | ConvertFrom-Json
$changed = $false
if (-not $json.'basicSettings.rootMode') {
  $json | Add-Member -NotePropertyName 'basicSettings.rootMode' -NotePropertyValue $true -Force
  $changed = $true
} else {
  $json.'basicSettings.rootMode' = $true
  $changed = $true
}
if ($json.'basicSettings.adbDebug' -ne 1) {
  $json | Add-Member -NotePropertyName 'basicSettings.adbDebug' -NotePropertyValue 1 -Force
  $changed = $true
} else {
  $json.'basicSettings.adbDebug' = 1
}

if ($changed) {
  $json | ConvertTo-Json -Depth 10 | Set-Content $config -Encoding UTF8
  Write-Host "enable_adb: wrote basicSettings.adbDebug=1 to $config"
} else {
  Write-Host "enable_adb: adbDebug already enabled"
}

$ldconsole = Join-Path $ldHome "ldconsole.exe"
$adb = Join-Path $ldHome "adb.exe"
& $ldconsole reboot --index $LdIndex | Out-Null
Write-Host "enable_adb: rebooting emulator..."
Start-Sleep -Seconds 40

& $adb kill-server 2>$null | Out-Null
& $adb start-server 2>&1 | Out-Null
foreach ($port in @(5555, 5557, 5554)) {
  & $adb connect ("127.0.0.1:" + $port) 2>&1 | Out-Null
}
$devices = & $adb devices 2>&1
Write-Host $devices
if ($devices -match "device") {
  Write-Host "enable_adb: SUCCESS"
  exit 0
}
Write-Host "enable_adb: still no device — wait longer and run: adb devices"
exit 1
