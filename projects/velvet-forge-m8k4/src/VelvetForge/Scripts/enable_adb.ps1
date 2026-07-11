# Enable LDPlayer ADB (Open local connection) — LDPlayer 9.5+ right-sidebar gear UI.
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
public class WinRect {
  [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int Left, Top, Right, Bottom; }
}
"@

function Click-At($x, $y) {
  [System.Windows.Forms.Cursor]::Position = New-Object System.Drawing.Point([int]$x, [int]$y)
  Start-Sleep -Milliseconds 220
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
  Start-Sleep -Seconds 12
  $p = Get-Process dnplayer -ErrorAction SilentlyContinue | Select-Object -First 1
}
if (-not $p) { throw "dnplayer not running" }

[LdWin]::SetForegroundWindow($p.MainWindowHandle) | Out-Null
Start-Sleep -Milliseconds 500

$rect = New-Object WinRect+RECT
[WinRect]::GetWindowRect($p.MainWindowHandle, [ref]$rect) | Out-Null
$w = $rect.Right - $rect.Left
$h = $rect.Bottom - $rect.Top
$L = $rect.Left
$T = $rect.Top
$R = $rect.Right
Write-Host "window ${w}x${h} @ $L,$T"

Click-At ($L + $w * 0.62) ($T + $h * 0.28)
Start-Sleep -Milliseconds 400

Click-At ($R - 28) ($T + 72)
Start-Sleep -Seconds 2

Click-At ($L + $w * 0.22) ($T + $h * 0.42)
Start-Sleep -Milliseconds 600

Click-At ($L + $w * 0.58) ($T + $h * 0.38)
Start-Sleep -Milliseconds 400

Click-At ($L + $w * 0.52) ($T + $h * 0.46)
Start-Sleep -Milliseconds 500
Click-At ($L + $w * 0.52) ($T + $h * 0.52)
Start-Sleep -Milliseconds 400

Click-At ($L + $w * 0.62) ($T + $h * 0.78)
Start-Sleep -Seconds 1
Click-At ($L + $w * 0.55) ($T + $h * 0.58)
Start-Sleep -Seconds 2

Write-Host "enable_adb: done - rebooting"
& $ldconsole reboot --index $LdIndex | Out-Null
