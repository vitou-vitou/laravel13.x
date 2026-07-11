param(
  [string]$Key = "F12"
)
Add-Type -AssemblyName System.Windows.Forms
$procs = Get-Process dnplayer -ErrorAction SilentlyContinue
if (-not $procs) { exit 1 }
$best = $null
$bestArea = 0
foreach ($p in $procs) {
  Add-Type @"
using System; using System.Runtime.InteropServices;
public class WRect { [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int L,T,R,B; } }
"@
  $r = New-Object WRect+RECT
  if ([WRect]::GetWindowRect($p.MainWindowHandle, [ref]$r)) {
    $area = ($r.R-$r.L)*($r.B-$r.T)
    if ($area -gt $bestArea) { $bestArea = $area; $best = $p }
  }
}
if (-not $best) { exit 1 }
Add-Type @"
using System; using System.Runtime.InteropServices;
public class FgWin { [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr hWnd); }
"@
[FgWin]::SetForegroundWindow($best.MainWindowHandle) | Out-Null
Start-Sleep -Milliseconds 300
if ($Key -eq "ESC") {
  [System.Windows.Forms.SendKeys]::SendWait("{ESC}")
} else {
  [System.Windows.Forms.SendKeys]::SendWait("{$Key}")
}
Write-Host "sent $Key to dnplayer"
