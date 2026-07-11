# Click LDPlayer sidebar (LDLDBroadScreenWndIE) to open Settings.
$ErrorActionPreference = "Stop"
Add-Type -AssemblyName System.Windows.Forms, System.Drawing
Add-Type @"
using System; using System.Text; using System.Runtime.InteropServices;
public class W {
  public delegate bool EP(IntPtr h, IntPtr l);
  [DllImport("user32.dll")] public static extern bool EnumWindows(EP e, IntPtr l);
  [DllImport("user32.dll")] public static extern int GetWindowText(IntPtr h, StringBuilder s, int c);
  [DllImport("user32.dll")] public static extern int GetClassName(IntPtr h, StringBuilder s, int c);
  [DllImport("user32.dll")] public static extern bool IsWindowVisible(IntPtr h);
  [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
  [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr h);
  [DllImport("user32.dll")] public static extern bool ShowWindow(IntPtr h, int n);
  [DllImport("user32.dll")] public static extern bool MoveWindow(IntPtr h, int x, int y, int w, int ht, bool repaint);
  [DllImport("user32.dll")] public static extern void mouse_event(int f, int x, int y, int b, int e);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int L,T,R,B; }
  public const int SW_RESTORE=9, LD=2, LU=4;
}
"@

function Click([int]$x, [int]$y) {
  [Windows.Forms.Cursor]::Position = New-Object Drawing.Point($x, $y)
  Start-Sleep -Milliseconds 200
  [W]::mouse_event([W]::LD, 0, 0, 0, 0)
  [W]::mouse_event([W]::LU, 0, 0, 0, 0)
}

function Get-Sidebar {
  $global:sb = $null
  [W]::EnumWindows({
    param($h, $l)
    if (-not [W]::IsWindowVisible($h)) { return $true }
    $c = New-Object Text.StringBuilder 256
    [W]::GetClassName($h, $c, 256) | Out-Null
    if ($c.ToString() -ne "LDLDBroadScreenWndIE") { return $true }
    $rr = New-Object W+RECT
    [W]::GetWindowRect($h, [ref]$rr) | Out-Null
    if (($rr.R - $rr.L) -lt 100) { return $true }
    $global:sb = $rr
    return $true
  }, [IntPtr]::Zero) | Out-Null
  return $global:sb
}

function Has-LdSettings {
  $found = $false
  [W]::EnumWindows({
    param($h, $l)
    if (-not [W]::IsWindowVisible($h)) { return $true }
    $t = New-Object Text.StringBuilder 256
    [W]::GetWindowText($h, $t, 256) | Out-Null
    if ($t.ToString() -ne "Settings") { return $true }
    $c = New-Object Text.StringBuilder 256
    [W]::GetClassName($h, $c, 256) | Out-Null
    if ($c.ToString() -match "ApplicationFrame|Windows.UI") { return $true }
    $script:found = $true
    return $true
  }, [IntPtr]::Zero) | Out-Null
  return $found
}

$ldHwnd = [IntPtr][int64]((& "D:/LDPlayer/LDPlayer9/ldconsole.exe" list2 2>&1 | Where-Object { $_ -match "^0," } | Select-Object -First 1) -split ",")[2]
[W]::ShowWindow($ldHwnd, [W]::SW_RESTORE) | Out-Null
[W]::MoveWindow($ldHwnd, 80, 40, 1320, 760, $true) | Out-Null
Start-Sleep -Milliseconds 500

# Dismiss keymap prompt if visible
$lr = New-Object W+RECT
[W]::GetWindowRect($ldHwnd, [ref]$lr) | Out-Null
Click ([int](($lr.L + $lr.R) / 2)) ([int]($lr.T + ($lr.B - $lr.T) * 0.58))
Start-Sleep -Milliseconds 500

$sb = Get-Sidebar
if (-not $sb) { throw "Sidebar LDLDBroadScreenWndIE not found" }
$sx = [int](($sb.L + $sb.R) / 2)
Write-Host ("Sidebar " + ($sb.R - $sb.L) + "x" + ($sb.B - $sb.T) + " at " + $sb.L + "," + $sb.T + " centerX=" + $sx)

# Gear is ~6th icon: scan y offsets
foreach ($dy in @(95, 130, 165, 200, 235, 270, 305, 340)) {
  $y = $sb.T + $dy
  Write-Host "try gear y=$y"
  Click $sx $y
  Start-Sleep -Seconds 1
  if (Has-LdSettings) { Write-Host "FOUND Settings at dy=$dy"; break }
}

[W]::EnumWindows({
  param($h, $l)
  if (-not [W]::IsWindowVisible($h)) { return $true }
  $t = New-Object Text.StringBuilder 256
  [W]::GetWindowText($h, $t, 256) | Out-Null
  if ($t.ToString() -eq "Settings") {
    $c = New-Object Text.StringBuilder 256
    [W]::GetClassName($h, $c, 256) | Out-Null
    $rr = New-Object W+RECT
    [W]::GetWindowRect($h, [ref]$rr) | Out-Null
    Write-Host ("Settings hwnd=" + $h + " class=" + $c.ToString() + " " + ($rr.R-$rr.L) + "x" + ($rr.B-$rr.T))
    $bmp = New-Object Drawing.Bitmap ($rr.R-$rr.L), ($rr.B-$rr.T)
    $g = [Drawing.Graphics]::FromImage($bmp)
    $g.CopyFromScreen($rr.L, $rr.T, 0, 0, (New-Object Drawing.Size(($rr.R-$rr.L), ($rr.B-$rr.T))))
    $bmp.Save("d:/laravel13.x/projects/amber-harbor-ll8b/data/screenshots/ld-settings-found.png")
  }
  return $true
}, [IntPtr]::Zero) | Out-Null
