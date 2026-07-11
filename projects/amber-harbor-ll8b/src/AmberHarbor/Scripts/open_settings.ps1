Add-Type -AssemblyName System.Windows.Forms
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
  Write-Host "click $x,$y"
  [Windows.Forms.Cursor]::Position = New-Object Drawing.Point($x, $y)
  Start-Sleep -Milliseconds 250
  [W]::mouse_event([W]::LD, 0, 0, 0, 0)
  [W]::mouse_event([W]::LU, 0, 0, 0, 0)
}

function List-LdWindows {
  [W]::EnumWindows({
    param($h, $l)
    if (-not [W]::IsWindowVisible($h)) { return $true }
    $c = New-Object Text.StringBuilder 256
    [W]::GetClassName($h, $c, 256) | Out-Null
    if ($c.ToString() -notmatch "LD") { return $true }
    $t = New-Object Text.StringBuilder 256
    [W]::GetWindowText($h, $t, 256) | Out-Null
    $rr = New-Object W+RECT
    [W]::GetWindowRect($h, [ref]$rr) | Out-Null
    Write-Host ("  hwnd=" + $h + " class=" + $c.ToString() + " title='" + $t.ToString() + "' " + ($rr.R-$rr.L) + "x" + ($rr.B-$rr.T) + " @ " + $rr.L + "," + $rr.T)
    return $true
  }, [IntPtr]::Zero) | Out-Null
}

$line = & "D:/LDPlayer/LDPlayer9/ldconsole.exe" list2 2>&1 | Where-Object { $_ -match "^0," } | Select-Object -First 1
$hwnd = [IntPtr][int64](($line -split ",")[2])
[W]::ShowWindow($hwnd, [W]::SW_RESTORE) | Out-Null
[W]::MoveWindow($hwnd, 100, 50, 1280, 720, $true) | Out-Null
Start-Sleep -Milliseconds 500
[W]::SetForegroundWindow($hwnd) | Out-Null
$r = New-Object W+RECT
[W]::GetWindowRect($hwnd, [ref]$r) | Out-Null
$w = $r.R - $r.L; $h = $r.B - $r.T
Write-Host "LDPlayer moved to $($r.L),$($r.T) ${w}x${h}"

# hamburger menu (three lines) top-right title bar
Click ([int]($r.L + $w * 0.91)) ([int]($r.T + 22))
Start-Sleep -Milliseconds 800
List-LdWindows
# click Settings in dropdown (approx)
Click ([int]($r.L + $w * 0.82)) ([int]($r.T + 95))
Start-Sleep -Seconds 2
Write-Host "After menu Settings click:"
List-LdWindows

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
    Write-Host ("SETTINGS hwnd=" + $h + " class=" + $c.ToString() + " " + ($rr.R-$rr.L) + "x" + ($rr.B-$rr.T) + " @ " + $rr.L + "," + $rr.T)
  }
  return $true
}, [IntPtr]::Zero) | Out-Null
