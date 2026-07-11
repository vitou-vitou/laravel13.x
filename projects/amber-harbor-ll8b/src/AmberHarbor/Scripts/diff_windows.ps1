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
  [DllImport("user32.dll")] public static extern void mouse_event(int f, int x, int y, int b, int e);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int L,T,R,B; }
  public const int LD=2, LU=4;
}
"@

function Click([int]$x,[int]$y){ [Windows.Forms.Cursor]::Position=New-Object Drawing.Point($x,$y); Start-Sleep -m 200; [W]::mouse_event([W]::LD,0,0,0,0); [W]::mouse_event([W]::LU,0,0,0,0) }
function Snap([string]$tag) {
  $global:snap = @()
  [W]::EnumWindows({ param($h,$l); if(-not [W]::IsWindowVisible($h)){return $true}; $t=New-Object Text.StringBuilder 256;[W]::GetWindowText($h,$t,256)|Out-Null; $c=New-Object Text.StringBuilder 256;[W]::GetClassName($h,$c,256)|Out-Null; $r=New-Object W+RECT;[W]::GetWindowRect($h,[ref]$r)|Out-Null; $w=$r.R-$r.L;$ht=$r.B-$r.T; if($w -gt 200 -and $ht -gt 200){ $global:snap += ($c.ToString()+":"+$t.ToString()+":"+$w+"x"+$ht) }; return $true },[IntPtr]::Zero)|Out-Null
}

$sb = $null
[W]::EnumWindows({ param($h,$l); $c=New-Object Text.StringBuilder 256;[W]::GetClassName($h,$c,256)|Out-Null; if($c.ToString()-eq "LDLDBroadScreenWndIE"){ $r=New-Object W+RECT;[W]::GetWindowRect($h,[ref]$r)|Out-Null; if(($r.R-$r.L)-gt 100){$script:sb=$r} }; return $true},[IntPtr]::Zero)|Out-Null
$sx = [int](($sb.L+$sb.R)/2)
Snap "before"
Click $sx ($sb.T+200)
Start-Sleep -Seconds 2
Snap "after"
$before = $global:snap
Snap "after2"
$after = $global:snap
Write-Host "NEW windows after gear click:"
$after | Where-Object { $before -notcontains $_ } | ForEach-Object { Write-Host "  $_" }
