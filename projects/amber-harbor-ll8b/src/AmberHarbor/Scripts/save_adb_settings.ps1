# Save LDPlayer settings + restart (when ADB already set to Open local connection).
param([int]$LdIndex = 0)

$ErrorActionPreference = "Stop"
Add-Type -AssemblyName System.Windows.Forms
Add-Type @"
using System; using System.Text; using System.Runtime.InteropServices;
public class WinApi {
  public delegate bool EnumProc(IntPtr h, IntPtr l);
  [DllImport("user32.dll")] public static extern bool EnumWindows(EnumProc e, IntPtr l);
  [DllImport("user32.dll")] public static extern int GetWindowText(IntPtr h, StringBuilder s, int c);
  [DllImport("user32.dll")] public static extern bool IsWindowVisible(IntPtr h);
  [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
  [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr h);
  [DllImport("user32.dll")] public static extern void mouse_event(int f, int x, int y, int b, int e);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int Left, Top, Right, Bottom; }
  public const int LD=2, LU=4;
}
"@

function Click-At($x, $y) {
  [Windows.Forms.Cursor]::Position = New-Object Drawing.Point([int]$x, [int]$y)
  Start-Sleep -Milliseconds 200
  [WinApi]::mouse_event([WinApi]::LD, 0, 0, 0, 0)
  [WinApi]::mouse_event([WinApi]::LU, 0, 0, 0, 0)
}

function Get-LdPlayerHome {
  foreach ($c in @($env:LDPLAYER_HOME, "D:\LDPlayer\LDPlayer9", "C:\Program Files\LDPlayer\LDPlayer9")) {
    if ($c -and (Test-Path (Join-Path $c "ldconsole.exe"))) { return $c }
  }
  throw "LDPlayer not found"
}

function Find-SettingsHwnd {
  $hwnd = [IntPtr]::Zero
  [WinApi]::EnumWindows({
    param($h, $l)
    if (-not [WinApi]::IsWindowVisible($h)) { return $true }
    $t = New-Object Text.StringBuilder 256
    [WinApi]::GetWindowText($h, $t, 256) | Out-Null
    if ($t.ToString() -eq "Settings") { $script:hwnd = $h }
    return $true
  }, [IntPtr]::Zero) | Out-Null
  return $hwnd
}

$ldHome = Get-LdPlayerHome
$ldconsole = Join-Path $ldHome "ldconsole.exe"
& $ldconsole modify --index $LdIndex --root 1 | Out-Null

$settings = Find-SettingsHwnd
if ($settings -eq [IntPtr]::Zero) {
  # Open gear from LDPlayer main window
  $p = Get-Process dnplayer -ErrorAction SilentlyContinue | Select-Object -First 1
  if (-not $p) {
    & $ldconsole launch --index $LdIndex | Out-Null
    Start-Sleep -Seconds 10
    $p = Get-Process dnplayer -ErrorAction SilentlyContinue | Select-Object -First 1
  }
  $r = New-Object WinApi+RECT
  [WinApi]::GetWindowRect($p.MainWindowHandle, [ref]$r) | Out-Null
  [WinApi]::SetForegroundWindow($p.MainWindowHandle) | Out-Null
  Click-At ($r.Right - 28) ($r.Top + 130)
  Start-Sleep -Seconds 2
  $settings = Find-SettingsHwnd
}

if ($settings -eq [IntPtr]::Zero) { throw "Settings window not found" }

$sr = New-Object WinApi+RECT
[WinApi]::GetWindowRect($settings, [ref]$sr) | Out-Null
$L = $sr.Left; $T = $sr.Top; $w = $sr.Right - $sr.Left; $h = $sr.Bottom - $sr.Top
Write-Host "Settings ${w}x${h} @ $L,$T"
[WinApi]::SetForegroundWindow($settings) | Out-Null
Start-Sleep -Milliseconds 400

# Advanced tab (ADB lives here on LDPlayer 9.5)
Click-At ($L + $w * 0.50) ($T + $h * 0.12)
Start-Sleep -Milliseconds 500

# ADB dropdown -> Open local connection (if not already)
Click-At ($L + $w * 0.55) ($T + $h * 0.52)
Start-Sleep -Milliseconds 400
Click-At ($L + $w * 0.55) ($T + $h * 0.58)
Start-Sleep -Milliseconds 400

# Save settings (green button, bottom center)
Click-At ($L + $w * 0.50) ($T + $h * 0.93)
Start-Sleep -Seconds 1

# Restart now on confirmation dialog
Click-At ($L + $w * 0.50) ($T + $h * 0.55)
Start-Sleep -Seconds 2

Write-Host "save_adb_settings: done - rebooting"
& $ldconsole reboot --index $LdIndex | Out-Null
