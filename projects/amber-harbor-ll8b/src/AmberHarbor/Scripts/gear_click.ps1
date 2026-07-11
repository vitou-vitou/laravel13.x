Add-Type -AssemblyName System.Windows.Forms
Add-Type @"
using System; using System.Text; using System.Runtime.InteropServices;
public class W {
  [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
  [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr h);
  [DllImport("user32.dll")] public static extern bool ShowWindow(IntPtr h, int n);
  [DllImport("user32.dll")] public static extern bool MoveWindow(IntPtr h, int x, int y, int w, int ht, bool repaint);
  [DllImport("user32.dll")] public static extern int GetClassName(IntPtr h, StringBuilder s, int c);
  [DllImport("user32.dll")] public static extern bool EnumWindows(EnumWindowsProc e, IntPtr l);
  public delegate bool EnumWindowsProc(IntPtr h, IntPtr l);
  [DllImport("user32.dll")] public static extern int GetWindowText(IntPtr h, StringBuilder s, int c);
  [DllImport("user32.dll")] public static extern bool IsWindowVisible(IntPtr h);
  [DllImport("user32.dll")] public static extern void mouse_event(int f, int x, int y, int b, int e);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int L,T,R,B; }
  public const int SW_RESTORE=9, LD=2, LU=4;
}
"@

function Click([int]$x,[int]$y){
  Write-Host "click $x,$y"
  [Windows.Forms.Cursor]::Position = New-Object Drawing.Point($x,$y)
  Start-Sleep -Milliseconds 250
  [W]::mouse_event([W]::LD,0,0,0,0)
  [W]::mouse_event([W]::LU,0,0,0,0)
}

$ld = [IntPtr][int64]((& "D:/LDPlayer/LDPlayer9/ldconsole.exe" list2 2>&1 | ?{$_ -match '^0,'}|select -first 1)-split ',')[2]
[W]::ShowWindow($ld,[W]::SW_RESTORE)|Out-Null
[W]::MoveWindow($ld,80,40,1320,760,$true)|Out-Null
Start-Sleep -m 400
[W]::SetForegroundWindow($ld)|Out-Null
$lr=New-Object W+RECT; [W]::GetWindowRect($ld,[ref]$lr)|Out-Null
$w=$lr.R-$lr.L; $h=$lr.B-$lr.T

# Close center ad X
Click ([int]($lr.L+$w*0.62)) ([int]($lr.T+$h*0.30))
Start-Sleep -m 500

$sidebar=[IntPtr]::Zero; $sbr=New-Object W+RECT
[W]::EnumWindows({param($h,$l); $c=New-Object Text.StringBuilder 256;[W]::GetClassName($h,$c,256)|Out-Null; if($c.ToString()-eq 'LDLDBroadScreenWndIE'){[W]::GetWindowRect($h,[ref]$script:sbr)|Out-Null; if(($script:sbr.R-$script:sbr.L)-gt 100){$script:sidebar=$h};}; return $true},[IntPtr]::Zero)|Out-Null
Write-Host ("sidebar hwnd=" + $sidebar + " at " + $sbr.L + "," + $sbr.T)
[W]::SetForegroundWindow($sidebar)|Out-Null
Start-Sleep -m 300
$sx=[int](($sbr.L+$sbr.R)/2)

foreach($dy in 175,200,225,250) {
  Click $sx ($sbr.T+$dy)
  Start-Sleep -Seconds 2
  $hit=$false
  [W]::EnumWindows({param($h,$l); if(-not [W]::IsWindowVisible($h)){return $true}; $t=New-Object Text.StringBuilder 256;[W]::GetWindowText($h,$t,256)|Out-Null; $c=New-Object Text.StringBuilder 256;[W]::GetClassName($h,$c,256)|Out-Null; if($t.ToString().Length -gt 0 -or $c.ToString() -match 'Setting|LDSetting'){ $r=New-Object W+RECT;[W]::GetWindowRect($h,[ref]$r)|Out-Null; $ww=$r.R-$r.L;$ht=$r.B-$r.T; if($c.ToString() -match 'Setting|LDSetting' -or ($t.ToString()-eq 'Settings' -and $c.ToString() -notmatch 'ApplicationFrame')){ Write-Host ("  OPENED: class="+$c.ToString()+" title="+$t.ToString()+" "+$ww+"x"+$ht); $script:hit=$true } }; return $true},[IntPtr]::Zero)|Out-Null
  if($hit){break}
  # close if opened wrong thing - press escape
  [System.Windows.Forms.SendKeys]::SendWait('{ESC}')
  Start-Sleep -m 300
}
