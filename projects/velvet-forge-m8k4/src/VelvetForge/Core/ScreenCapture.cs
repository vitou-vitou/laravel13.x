using System.Drawing;
using System.Drawing.Imaging;
using System.Runtime.InteropServices;

namespace VelvetForge.Core;

public static class ScreenCapture
{
  [DllImport("user32.dll")]
  private static extern bool PrintWindow(IntPtr hwnd, IntPtr hdcBlt, int nFlags);

  public static bool CaptureBindWindow(LdPlayer ld, string outPath)
  {
    FarmPaths.EnsureDataDirs();
    var info = ld.GetInstanceInfo();
    if (info is null || info.BindHwnd == 0) return false;

    var hwnd = new IntPtr(info.BindHwnd);
    using var bmp = new Bitmap(info.Width, info.Height, PixelFormat.Format32bppArgb);
    using var g = Graphics.FromImage(bmp);
    var hdc = g.GetHdc();
    try
    {
      PrintWindow(hwnd, hdc, 2);
    }
    finally
    {
      g.ReleaseHdc(hdc);
    }
    Directory.CreateDirectory(Path.GetDirectoryName(outPath)!);
    bmp.Save(outPath, ImageFormat.Png);
    return File.Exists(outPath);
  }
}
