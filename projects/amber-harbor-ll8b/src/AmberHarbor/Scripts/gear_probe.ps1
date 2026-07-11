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

$line = & "D:/LDPlayer/LDPlayer9/ldconsole.exe" list2 2>&1 | Where-Object { $_ -match "^0," } | Select-Object -First 1
$hwnd = [IntPtr][int64](($line -split ",")[2])
[W]::SetForegroundWindow($hwnd) | Out-Null
$r = New-Object W+RECT
[W]::GetWindowRect($hwnd, [ref]$r) | Out-Null
Write-Host "LDPlayer $hwnd at $($r.L),$($r.T) $($r.R-$r.L)x$($r.B-$r.T)"
Click ([int]($r.R - 22)) ([int]($r.T + 230))
Start-Sleep -Seconds 2

Write-Host "--- all visible windows (class/title) ---"
[W]::EnumWindows({
  param($h, $l)
  if (-not [W]::IsWindowVisible($h)) { return $true }
  $t = New-Object Text.StringBuilder 256
  [W]::GetWindowText($h, $t, 256) | Out-Null
  $title = $t.ToString()
  $c = New-Object Text.StringBuilder 256
  [W]::GetClassName($h, $c, 256) | Out-Null
  $cls = $c.ToString()
  if ($cls -match "LD|Qt|Setting|dnplayer|Chrome_Widget" -or $title -match "Setting|LDPlayer") {
    $rr = New-Object W+RECT
    [W]::GetWindowRect($h, [ref]$rr) | Out-Null
  $w = $rr.R - $rr.L; $ht = $rr.B - $rr.T
    if ($w -gt 50 -and $ht -gt 50) {
      Write-Host ("hwnd=" + $h + " class=" + $cls + " title='" + $title + "' " + $w + "x" + $ht)
    }
  }
  return $true
}, [IntPtr]::Zero) | Out-Null
