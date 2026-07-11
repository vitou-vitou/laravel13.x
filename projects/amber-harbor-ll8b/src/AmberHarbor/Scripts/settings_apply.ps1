param([switch]$Apply)
$ErrorActionPreference = "Stop"
Add-Type -AssemblyName System.Windows.Forms, System.Drawing
Add-Type @"
using System; using System.Text; using System.Runtime.InteropServices;
public class W {
  public delegate bool EP(IntPtr h, IntPtr l);
  [DllImport("user32.dll")] public static extern bool EnumWindows(EP e, IntPtr l);
  [DllImport("user32.dll")] public static extern int GetWindowText(IntPtr h, StringBuilder s, int c);
  [DllImport("user32.dll")] public static extern bool IsWindowVisible(IntPtr h);
  [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
  [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr h);
  [DllImport("user32.dll")] public static extern void mouse_event(int f, int x, int y, int b, int e);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int L,T,R,B; }
  public const int LD=2, LU=4;
}
"@

function Click([int]$x, [int]$y) {
  [Windows.Forms.Cursor]::Position = New-Object Drawing.Point($x, $y)
  Start-Sleep -Milliseconds 250
  [W]::mouse_event([W]::LD, 0, 0, 0, 0)
  [W]::mouse_event([W]::LU, 0, 0, 0, 0)
}

function Get-SettingsHwnd {
  $global:ldSettingsCandidates = @()
  [W]::EnumWindows({
    param($h, $l)
    if (-not [W]::IsWindowVisible($h)) { return $true }
    $t = New-Object Text.StringBuilder 256
    [W]::GetWindowText($h, $t, 256) | Out-Null
    if ($t.ToString() -eq "Settings") {
      $rr = New-Object W+RECT
      [W]::GetWindowRect($h, [ref]$rr) | Out-Null
      $w = $rr.R - $rr.L; $ht = $rr.B - $rr.T
      $global:ldSettingsCandidates += [PSCustomObject]@{ Hwnd = $h; W = $w; H = $ht; Left = $rr.L; Top = $rr.T }
    }
    return $true
  }, [IntPtr]::Zero) | Out-Null
  $filtered = @($global:ldSettingsCandidates | Where-Object { $_.W -ge 500 -and $_.W -le 900 })
  if ($filtered.Count -eq 0) { return $null }
  return ($filtered | Select-Object -First 1)
}

function Shot($hwnd, $name) {
  $sr = New-Object W+RECT
  [W]::GetWindowRect($hwnd, [ref]$sr) | Out-Null
  $L = $sr.L; $T = $sr.T; $sw = $sr.R - $sr.L; $sh = $sr.B - $sr.T
  $out = "d:/laravel13.x/projects/amber-harbor-ll8b/data/screenshots/$name"
  $bmp = New-Object Drawing.Bitmap $sw, $sh
  $g = [Drawing.Graphics]::FromImage($bmp)
  $g.CopyFromScreen($L, $T, 0, 0, (New-Object Drawing.Size($sw, $sh)))
  $bmp.Save($out)
  Write-Host "saved $out (${sw}x${sh})"
  return @{ L = $L; T = $T; W = $sw; H = $sh }
}

$s = Get-SettingsHwnd
if (-not $s) {
  $line = & "D:/LDPlayer/LDPlayer9/ldconsole.exe" list2 2>&1 | Where-Object { $_ -match "^0," } | Select-Object -First 1
  $hwnd = [IntPtr][int64](($line -split ",")[2])
  [W]::SetForegroundWindow($hwnd) | Out-Null
  $r = New-Object W+RECT; [W]::GetWindowRect($hwnd, [ref]$r) | Out-Null
  Click ([int]($r.R - 22)) ([int]($r.T + 230))
  Start-Sleep -Seconds 2
  $s = Get-SettingsHwnd
}
if (-not $s) { throw "Settings not found" }

[W]::SetForegroundWindow($s.Hwnd) | Out-Null
Start-Sleep -Milliseconds 400
$rect = Shot $s.Hwnd "settings-step0.png"

if ($Apply) {
  $L = $rect.L; $T = $rect.T; $sw = $rect.W; $sh = $rect.H
  # Advanced tab (rightmost) — 756x766 calibrated
  Click ([int]($L + $sw * 0.90)) ([int]($T + $sh * 0.075))
  Start-Sleep -Milliseconds 700
  Shot $s.Hwnd "settings-step1-advanced.png"

  # Root ON (toggle if off)
  Click ([int]($L + $sw * 0.72)) ([int]($T + $sh * 0.365))
  Start-Sleep -Milliseconds 400

  # ADB dropdown
  Click ([int]($L + $sw * 0.55)) ([int]($T + $sh * 0.522))
  Start-Sleep -Milliseconds 500
  Click ([int]($L + $sw * 0.55)) ([int]($T + $sh * 0.60))
  Start-Sleep -Milliseconds 500
  Shot $s.Hwnd "settings-step2-adb.png"

  # Save settings (left button)
  Click ([int]($L + $sw * 0.22)) ([int]($T + $sh * 0.966))
  Start-Sleep -Seconds 2
  Shot $s.Hwnd "settings-step3-after-save.png"

  # Restart now (yellow button, right of Save)
  Click ([int]($L + $sw * 0.40)) ([int]($T + $sh * 0.966))
  Start-Sleep -Seconds 2

  # Confirm dialog Restart now
  Click ([int]($L + $sw * 0.50)) ([int]($T + $sh * 0.55))
  Write-Host "Applied Save + Restart"
}
