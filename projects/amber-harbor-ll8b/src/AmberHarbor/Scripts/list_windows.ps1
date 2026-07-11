Add-Type @"
using System; using System.Text; using System.Runtime.InteropServices;
public class W {
  public delegate bool EP(IntPtr h, IntPtr l);
  [DllImport("user32.dll")] public static extern bool EnumWindows(EP e, IntPtr l);
  [DllImport("user32.dll")] public static extern int GetWindowText(IntPtr h, StringBuilder s, int c);
  [DllImport("user32.dll")] public static extern int GetClassName(IntPtr h, StringBuilder s, int c);
  [DllImport("user32.dll")] public static extern bool IsWindowVisible(IntPtr h);
  [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int L,T,R,B; }
}
"@
[W]::EnumWindows({
  param($h, $l)
  if (-not [W]::IsWindowVisible($h)) { return $true }
  $t = New-Object Text.StringBuilder 256
  [W]::GetWindowText($h, $t, 256) | Out-Null
  $title = $t.ToString()
  if ($title -match "Setting|LDPlayer") {
    $c = New-Object Text.StringBuilder 256
    [W]::GetClassName($h, $c, 256) | Out-Null
    $r = New-Object W+RECT
    [W]::GetWindowRect($h, [ref]$r) | Out-Null
    Write-Host ("hwnd=" + $h + " class=" + $c.ToString() + " title='" + $title + "' " + ($r.R-$r.L) + "x" + ($r.B-$r.T) + " @ " + $r.L + "," + $r.T)
  }
  return $true
}, [IntPtr]::Zero) | Out-Null
