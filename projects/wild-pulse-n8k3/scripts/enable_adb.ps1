# Enable LDPlayer ADB via title-bar menu (gear / hamburger near Remote LDPlayer).
param(
  [int]$LdIndex = 0
)

$ErrorActionPreference = "Stop"
Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

Add-Type @"
using System;
using System.Runtime.InteropServices;
public class LdWin {
  [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr hWnd);
  [DllImport("user32.dll")] public static extern void mouse_event(int f, int x, int y, int b, int e);
  public const int LDOWN=2, LUP=4;
}
"@

function Click-At($x, $y) {
  [System.Windows.Forms.Cursor]::Position = New-Object System.Drawing.Point([int]$x, [int]$y)
  Start-Sleep -Milliseconds 200
  [LdWin]::mouse_event([LdWin]::LDOWN, 0, 0, 0, 0)
  [LdWin]::mouse_event([LdWin]::LUP, 0, 0, 0, 0)
}

function Get-LdPlayerHome {
  foreach ($c in @($env:LDPLAYER_HOME, "D:\LDPlayer\LDPlayer9", "C:\Program Files\LDPlayer\LDPlayer9")) {
    if ($c -and (Test-Path (Join-Path $c "ldconsole.exe"))) { return $c }
  }
  throw "LDPlayer not found"
}

$ldHome = Get-LdPlayerHome
$ldconsole = Join-Path $ldHome "ldconsole.exe"
& $ldconsole modify --index $LdIndex --root 1 | Out-Null

$p = Get-Process dnplayer -ErrorAction SilentlyContinue | Select-Object -First 1
if (-not $p) {
  & $ldconsole launch --index $LdIndex | Out-Null
  Start-Sleep -Seconds 10
  $p = Get-Process dnplayer -ErrorAction SilentlyContinue | Select-Object -First 1
}
if (-not $p) { throw "dnplayer not running" }

[LdWin]::SetForegroundWindow($p.MainWindowHandle) | Out-Null
Start-Sleep -Milliseconds 400

Add-Type @"
using System; using System.Runtime.InteropServices;
public class WinRect {
  [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int Left, Top, Right, Bottom; }
}
"@
$rect = New-Object WinRect+RECT
[WinRect]::GetWindowRect($p.MainWindowHandle, [ref]$rect) | Out-Null
$w = $rect.Right - $rect.Left
$h = $rect.Bottom - $rect.Top
$L = $rect.Left
$T = $rect.Top
Write-Host "window ${w}x${h} @ $L,$T"

# Title-bar menu (three lines) left of Remote LDPlayer
Click-At ($L + $w * 0.78) ($T + 28)
Start-Sleep -Seconds 2

# Settings item in dropdown
Click-At ($L + $w * 0.70) ($T + 90)
Start-Sleep -Seconds 2

# Other settings tab
Click-At ($L + $w * 0.58) ($T + $h * 0.20)
Start-Sleep -Milliseconds 500

# Root ON
Click-At ($L + $w * 0.68) ($T + $h * 0.36)
Start-Sleep -Milliseconds 300

# ADB dropdown
Click-At ($L + $w * 0.52) ($T + $h * 0.44)
Start-Sleep -Milliseconds 400
# Open local connection option
Click-At ($L + $w * 0.52) ($T + $h * 0.50)
Start-Sleep -Milliseconds 400

# Save settings
Click-At ($L + $w * 0.68) ($T + $h * 0.86)
Start-Sleep -Seconds 1
# Restart now
Click-At ($L + $w * 0.55) ($T + $h * 0.55)
Start-Sleep -Seconds 2

Write-Host "enable_adb: done - rebooting"
& $ldconsole reboot --index $LdIndex | Out-Null
