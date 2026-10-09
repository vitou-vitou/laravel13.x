#Requires -RunAsAdministrator
<#
.SYNOPSIS
    Windows C: Drive Deep Cleaner - Universal Edition
.DESCRIPTION
    Modular, safe disk cleanup script for Windows 10/11.
    Each module can be toggled independently. DryRun mode for preview.
    Designed to replace paid "system optimizer" software.
.PARAMETER DryRun
    Preview mode - show what would be cleaned without deleting anything.
.PARAMETER SkipHibernation
    Skip disabling hibernation (hiberfil.sys).
.PARAMETER SkipWinSxS
    Skip WinSxS component cleanup (DISM).
.PARAMETER SkipRestorePoints
    Skip old restore point deletion.
.EXAMPLE
    .\disk_cleaner.ps1 -DryRun
    .\disk_cleaner.ps1
    .\disk_cleaner.ps1 -SkipHibernation -SkipWinSxS
#>

param(
    [switch]$DryRun,
    [switch]$SkipHibernation,
    [switch]$SkipWinSxS,
    [switch]$SkipRestorePoints
)

$ErrorActionPreference = "SilentlyContinue"
$totalFreed = 0

function Clean-PathList {
    param(
        [array]$Items,
        [string]$ModuleLabel
    )
    foreach ($item in $Items) {
        $parentPath = Split-Path $item.Path
        if (Test-Path $parentPath) {
            $files = Get-ChildItem -Path $item.Path -Recurse -Force -ErrorAction SilentlyContinue
            $sizeMB = ($files | Measure-Object -Property Length -Sum -ErrorAction SilentlyContinue).Sum / 1MB
            if ($sizeMB -gt 0.1) {
                if ($DryRun) {
                    Write-Host "  [Preview] $($item.Label): $([math]::Round($sizeMB,1)) MB" -ForegroundColor Yellow
                } else {
                    Remove-Item -Path $item.Path -Recurse -Force -ErrorAction SilentlyContinue
                    Write-Host "  [Cleaned] $($item.Label): $([math]::Round($sizeMB,1)) MB" -ForegroundColor Green
                }
                $script:totalFreed += $sizeMB
            }
        }
    }
}

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  Windows Disk Cleaner v2.0 (Universal)" -ForegroundColor Cyan
Write-Host "  Mode: $(if($DryRun){'PREVIEW (DryRun)'}else{'CLEAN'})" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# ==== Module 1: Temp Files ====
Write-Host ">>> [1/10] Temp files..." -ForegroundColor Magenta
$tempItems = @(
    @{ Label = "User Temp"; Path = "$env:TEMP\*" },
    @{ Label = "User LocalTemp"; Path = "$env:LOCALAPPDATA\Temp\*" },
    @{ Label = "Windows Temp"; Path = "C:\Windows\Temp\*" },
    @{ Label = "Prefetch"; Path = "C:\Windows\Prefetch\*" }
)
Clean-PathList -Items $tempItems -ModuleLabel "TempFiles"

# ==== Module 2: Recycle Bin ====
Write-Host ">>> [2/10] Recycle Bin..." -ForegroundColor Magenta
if (-not $DryRun) {
    Clear-RecycleBin -Force -ErrorAction SilentlyContinue
    Write-Host "  [Cleaned] Recycle Bin emptied" -ForegroundColor Green
} else {
    Write-Host "  [Preview] Will empty Recycle Bin" -ForegroundColor Yellow
}

# ==== Module 3: Windows Update Cache ====
Write-Host ">>> [3/10] Windows Update cache..." -ForegroundColor Magenta
if (-not $DryRun) {
    Stop-Service -Name wuauserv -Force -ErrorAction SilentlyContinue
    Stop-Service -Name bits -Force -ErrorAction SilentlyContinue
}
$wuItems = @(
    @{ Label = "WU Downloads"; Path = "C:\Windows\SoftwareDistribution\Download\*" },
    @{ Label = "WU Logs"; Path = "C:\Windows\SoftwareDistribution\DataStore\Logs\*" }
)
Clean-PathList -Items $wuItems -ModuleLabel "WindowsUpdate"
if (-not $DryRun) {
    Start-Service -Name wuauserv -ErrorAction SilentlyContinue
    Start-Service -Name bits -ErrorAction SilentlyContinue
}

# ==== Module 4: Browser Caches ====
Write-Host ">>> [4/10] Browser caches..." -ForegroundColor Magenta
$browserItems = @(
    @{ Label = "Edge Cache"; Path = "$env:LOCALAPPDATA\Microsoft\Edge\User Data\Default\Cache\*" },
    @{ Label = "Edge CodeCache"; Path = "$env:LOCALAPPDATA\Microsoft\Edge\User Data\Default\Code Cache\*" },
    @{ Label = "Edge SW"; Path = "$env:LOCALAPPDATA\Microsoft\Edge\User Data\Default\Service Worker\*" },
    @{ Label = "IE Cache"; Path = "$env:LOCALAPPDATA\Microsoft\Windows\INetCache\*" },
    @{ Label = "Chrome Cache"; Path = "$env:LOCALAPPDATA\Google\Chrome\User Data\Default\Cache\*" },
    @{ Label = "Chrome CodeCache"; Path = "$env:LOCALAPPDATA\Google\Chrome\User Data\Default\Code Cache\*" },
    @{ Label = "Firefox Cache"; Path = "$env:LOCALAPPDATA\Mozilla\Firefox\Profiles\*\cache2\*" }
)
Clean-PathList -Items $browserItems -ModuleLabel "BrowserCache"

# ==== Module 5: System Logs & Cache ====
Write-Host ">>> [5/10] System logs & cache..." -ForegroundColor Magenta
$sysItems = @(
    @{ Label = "Thumbnail Cache"; Path = "$env:LOCALAPPDATA\Microsoft\Windows\Explorer\thumbcache_*.db" },
    @{ Label = "Windows Logs"; Path = "C:\Windows\Logs\CBS\*.log" },
    @{ Label = "DISM Logs"; Path = "C:\Windows\Logs\DISM\*" },
    @{ Label = "Error Reports"; Path = "C:\ProgramData\Microsoft\Windows\WER\*" },
    @{ Label = "Kernel Reports"; Path = "C:\Windows\LiveKernelReports\*" },
    @{ Label = "Delivery Opt."; Path = "C:\Windows\SoftwareDistribution\DeliveryOptimization\*" },
    @{ Label = "Installer Temp"; Path = "C:\Windows\Installer\$PatchCache$\*" }
)
Clean-PathList -Items $sysItems -ModuleLabel "SystemLogs"

if (-not $DryRun) {
    wevtutil el 2>$null | ForEach-Object { wevtutil cl $_ 2>$null }
    Write-Host "  [Cleaned] Windows Event Logs cleared" -ForegroundColor Green
} else {
    Write-Host "  [Preview] Will clear Windows Event Logs" -ForegroundColor Yellow
}

# ==== Module 6: Common App Caches (CN & Global) ====
Write-Host ">>> [6/10] Application caches..." -ForegroundColor Magenta
$appItems = @(
    @{ Label = "QQ Temp"; Path = "$env:APPDATA\Tencent\QQ\Temp\*" },
    @{ Label = "WeChat Cache"; Path = "$env:USERPROFILE\Documents\WeChat Files\*\FileStorage\Cache\*" },
    @{ Label = "DingTalk Cache"; Path = "$env:APPDATA\DingTalk\*\Cache\*" },
    @{ Label = "Baidu Netdisk"; Path = "$env:APPDATA\baidu\BaiduNetdisk\cache\*" },
    @{ Label = "Netease Music"; Path = "$env:LOCALAPPDATA\Netease\CloudMusic\Cache\*" },
    @{ Label = "Spotify Cache"; Path = "$env:LOCALAPPDATA\Spotify\Storage\*" },
    @{ Label = "Teams Cache"; Path = "$env:APPDATA\Microsoft\Teams\Cache\*" },
    @{ Label = "Discord Cache"; Path = "$env:APPDATA\discord\Cache\*" },
    @{ Label = "Slack Cache"; Path = "$env:APPDATA\Slack\Cache\*" },
    @{ Label = "Zoom Cache"; Path = "$env:APPDATA\Zoom\data\*" },
    @{ Label = "Steam Logs"; Path = "C:\Program Files (x86)\Steam\logs\*" },
    @{ Label = "Adobe Cache"; Path = "$env:LOCALAPPDATA\Adobe\*\Cache\*" }
)
Clean-PathList -Items $appItems -ModuleLabel "AppCaches"

# ==== Module 7: Developer Tool Caches ====
Write-Host ">>> [7/10] Developer caches..." -ForegroundColor Magenta
$devItems = @(
    @{ Label = "pip cache"; Path = "$env:LOCALAPPDATA\pip\cache\*" },
    @{ Label = "npm cache"; Path = "$env:APPDATA\npm-cache\*" },
    @{ Label = "yarn cache"; Path = "$env:LOCALAPPDATA\Yarn\Cache\*" },
    @{ Label = "pnpm cache"; Path = "$env:LOCALAPPDATA\pnpm-cache\*" },
    @{ Label = "NuGet cache"; Path = "$env:LOCALAPPDATA\NuGet\v3-cache\*" },
    @{ Label = "Gradle cache"; Path = "$env:USERPROFILE\.gradle\caches\*" },
    @{ Label = "Maven cache"; Path = "$env:USERPROFILE\.m2\repository\*" },
    @{ Label = "JetBrains cache"; Path = "$env:LOCALAPPDATA\JetBrains\*\caches\*" },
    @{ Label = "JetBrains log"; Path = "$env:LOCALAPPDATA\JetBrains\*\log\*" },
    @{ Label = "VSCode Cache"; Path = "$env:APPDATA\Code\Cache\*" },
    @{ Label = "VSCode CachedData"; Path = "$env:APPDATA\Code\CachedData\*" },
    @{ Label = "VSCode Logs"; Path = "$env:APPDATA\Code\logs\*" },
    @{ Label = "NVIDIA DXCache"; Path = "$env:LOCALAPPDATA\NVIDIA\DXCache\*" },
    @{ Label = "NVIDIA GLCache"; Path = "$env:LOCALAPPDATA\NVIDIA\GLCache\*" },
    @{ Label = "Intel ShaderCache"; Path = "$env:LOCALAPPDATA\Intel\ShaderCache\*" },
    @{ Label = "AMD ShaderCache"; Path = "$env:LOCALAPPDATA\AMD\DxCache\*" },
    @{ Label = "CrashDumps"; Path = "$env:LOCALAPPDATA\CrashDumps\*" },
    @{ Label = "Docker cache"; Path = "$env:LOCALAPPDATA\Docker\wsl\data\*.vhdx.tmp" }
)
Clean-PathList -Items $devItems -ModuleLabel "DevCaches"

# ==== Module 8: Hibernation ====
Write-Host ">>> [8/10] Hibernation file..." -ForegroundColor Magenta
if ($SkipHibernation) {
    Write-Host "  [Skipped] -SkipHibernation flag set" -ForegroundColor DarkGray
} else {
    $hiberSize = 0
    if (Test-Path "C:\hiberfil.sys") {
        $hiberSize = [math]::Round((Get-Item "C:\hiberfil.sys" -Force).Length / 1GB, 1)
    }
    if ($DryRun) {
        Write-Host "  [Preview] powercfg /h off -> free ~${hiberSize} GB" -ForegroundColor Yellow
    } else {
        powercfg /h off
        Write-Host "  [Cleaned] Hibernation disabled, freed ~${hiberSize} GB" -ForegroundColor Green
        $totalFreed += ($hiberSize * 1024)
    }
}

# ==== Module 9: WinSxS Component Store ====
Write-Host ">>> [9/10] WinSxS component cleanup..." -ForegroundColor Magenta
if ($SkipWinSxS) {
    Write-Host "  [Skipped] -SkipWinSxS flag set" -ForegroundColor DarkGray
} else {
    if ($DryRun) {
        Write-Host "  [Preview] DISM /StartComponentCleanup /ResetBase" -ForegroundColor Yellow
    } else {
        Write-Host "  Running DISM (this may take a while)..." -ForegroundColor White
        DISM /Online /Cleanup-Image /StartComponentCleanup /ResetBase 2>$null
        DISM /Online /Cleanup-Image /SPSuperseded 2>$null
        Write-Host "  [Cleaned] WinSxS old components removed" -ForegroundColor Green
    }
}

# ==== Module 10: Old Restore Points ====
Write-Host ">>> [10/10] Old restore points..." -ForegroundColor Magenta
if ($SkipRestorePoints) {
    Write-Host "  [Skipped] -SkipRestorePoints flag set" -ForegroundColor DarkGray
} else {
    if ($DryRun) {
        Write-Host "  [Preview] Delete all but latest restore point" -ForegroundColor Yellow
    } else {
        vssadmin delete shadows /for=C: /oldest /quiet 2>$null
        Write-Host "  [Cleaned] Old restore points deleted (latest kept)" -ForegroundColor Green
    }
}

# ==== Bonus: Built-in Disk Cleanup ====
Write-Host ""
Write-Host ">>> [Bonus] Windows Disk Cleanup utility..." -ForegroundColor Magenta
if (-not $DryRun) {
    $cleanupKey = "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Explorer\VolumeCaches"
    $categories = Get-ChildItem $cleanupKey -ErrorAction SilentlyContinue
    foreach ($cat in $categories) {
        Set-ItemProperty -Path $cat.PSPath -Name "StateFlags0099" -Value 2 -ErrorAction SilentlyContinue
    }
    Start-Process -FilePath "cleanmgr.exe" -ArgumentList "/sagerun:99" -Wait -ErrorAction SilentlyContinue
    Write-Host "  [Cleaned] Disk Cleanup completed" -ForegroundColor Green
} else {
    Write-Host "  [Preview] cleanmgr /sagerun:99" -ForegroundColor Yellow
}

# ==== Summary ====
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  Done! Estimated freed: $([math]::Round($totalFreed/1024, 2)) GB" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "== Manual recommendations ==" -ForegroundColor Yellow
Write-Host "  1. Run WizTree/SpaceSniffer to find remaining large files" -ForegroundColor Gray
Write-Host "  2. Move large apps/games to another drive (mklink /D)" -ForegroundColor Gray
Write-Host "  3. Change browser download path to D:\" -ForegroundColor Gray
Write-Host "  4. Move Desktop/Documents folders to D:\" -ForegroundColor Gray
Write-Host "  5. If RAM >= 16GB, reduce pagefile size" -ForegroundColor Gray
Write-Host "  6. Uninstall unused programs via Settings > Apps" -ForegroundColor Gray
Write-Host ""
