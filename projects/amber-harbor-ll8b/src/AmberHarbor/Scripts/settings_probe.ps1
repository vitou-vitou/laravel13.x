# Quick: open Settings, screenshot, apply Save+Restart
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
  Start-Sleep -Milliseconds 200
  [W]::mouse_event([W]::LD, 0, 0, 0, 0)
  [W]::mouse_event([W]::LU, 0, 0, 0, 0)
}

function Find-Settings {
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
      $global:ldSettingsCandidates += [PSCustomObject]@{ Hwnd = $h; W = $w; H = $ht; Area = $w * $ht; Left = $rr.L; Top = $rr.T }
    }
    return $true
  }, [IntPtr]::Zero) | Out-Null
  Write-Host ("Settings candidates: " + ($global:ldSettingsCandidates | ForEach-Object { "$($_.W)x$($_.H)@$($_.Left),$($_.Top)" }) -join "; ")
  if ($global:ldSettingsCandidates.Count -eq 0) { return [IntPtr]::Zero }
  $filtered = @($global:ldSettingsCandidates | Where-Object { $_.W -ge 500 -and $_.W -le 900 -and $_.H -le 900 })
  if ($filtered.Count -gt 0) {
    return ($filtered | Sort-Object Area -Descending | Select-Object -First 1).Hwnd
  }
  return ($global:ldSettingsCandidates | Sort-Object Area -Descending | Select-Object -First 1).Hwnd
}

$line = & "D:/LDPlayer/LDPlayer9/ldconsole.exe" list2 2>&1 | Where-Object { $_ -match "^0," } | Select-Object -First 1
$hwnd = [IntPtr][int64](($line -split ",")[2])
[W]::SetForegroundWindow($hwnd) | Out-Null
$r = New-Object W+RECT
[W]::GetWindowRect($hwnd, [ref]$r) | Out-Null

$settings = Find-Settings
if ($settings -eq [IntPtr]::Zero) {
  Click ($r.R - 22) ($r.T + 230)
  Start-Sleep -Seconds 2
  $settings = Find-Settings
}
if ($settings -eq [IntPtr]::Zero) { throw "Settings not found" }

$sr = New-Object W+RECT
[W]::GetWindowRect($settings, [ref]$sr) | Out-Null
$L = $sr.L; $T = $sr.T; $sw = $sr.R - $sr.L; $sh = $sr.B - $sr.T
Write-Host "Settings ${sw}x${sh} @ $L,$T"

$out = "d:/laravel13.x/projects/amber-harbor-ll8b/data/screenshots/settings-now.png"
$bmp = New-Object Drawing.Bitmap $sw, $sh
$g = [Drawing.Graphics]::FromImage($bmp)
$g.CopyFromScreen($L, $T, 0, 0, (New-Object Drawing.Size($sw, $sh)))
$bmp.Save($out)
Write-Host "saved $out"

if ($Apply) {
  [W]::SetForegroundWindow($settings) | Out-Null
  Start-Sleep -Milliseconds 400
  Click ([int]($L + $sw * 0.88)) ([int]($T + $sh * 0.078))
  Start-Sleep -Milliseconds 500
  Click ([int]($L + $sw * 0.72)) ([int]($T + $sh * 0.365))
  Start-Sleep -Milliseconds 300
  Click ([int]($L + $sw * 0.55)) ([int]($T + $sh * 0.522))
  Start-Sleep -Milliseconds 400
  Click ([int]($L + $sw * 0.55)) ([int]($T + $sh * 0.60))
  Start-Sleep -Milliseconds 400
  Click ([int]($L + $sw * 0.18)) ([int]($T + $sh * 0.966))
  Start-Sleep -Seconds 1
  Click ([int]($L + $sw * 0.38)) ([int]($T + $sh * 0.966))
  Start-Sleep -Seconds 2
  Click ([int]($L + $sw * 0.50)) ([int]($T + $sh * 0.55))
  Write-Host "Save+Restart clicked"
}
