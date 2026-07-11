param(
  [Parameter(Mandatory=$true)][string]$OutPath
)
Add-Type -AssemblyName System.Drawing
$procs = Get-Process dnplayer -ErrorAction SilentlyContinue
if (-not $procs) { exit 1 }

Add-Type @"
using System;
using System.Runtime.InteropServices;
public class CapRect {
  [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr h, out RECT r);
  [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr hWnd);
  [StructLayout(LayoutKind.Sequential)] public struct RECT { public int Left, Top, Right, Bottom; }
}
"@

$best = $null
$bestArea = 0
$bestHandle = [IntPtr]::Zero
foreach ($p in $procs) {
  $title = $p.MainWindowTitle
  if ($title -notmatch 'LDPlayer|Mobile') { continue }
  $r = New-Object CapRect+RECT
  if (-not [CapRect]::GetWindowRect($p.MainWindowHandle, [ref]$r)) { continue }
  $w = $r.Right - $r.Left
  $h = $r.Bottom - $r.Top
  $area = $w * $h
  if ($area -gt $bestArea -and $w -gt 400 -and $h -gt 300) {
    $bestArea = $area
    $best = $r
    $bestHandle = $p.MainWindowHandle
  }
}
if (-not $best) { exit 1 }

[CapRect]::SetForegroundWindow($bestHandle) | Out-Null
Start-Sleep -Milliseconds 350

$w = $best.Right - $best.Left
$h = $best.Bottom - $best.Top
$bmp = New-Object System.Drawing.Bitmap $w, $h
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.CopyFromScreen($best.Left, $best.Top, 0, 0, (New-Object System.Drawing.Size($w, $h)))
$bmp.Save($OutPath)
Write-Host "saved $OutPath (${w}x${h})"
