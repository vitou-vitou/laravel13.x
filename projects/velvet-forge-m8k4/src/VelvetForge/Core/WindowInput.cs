using System.Runtime.InteropServices;

namespace VelvetForge.Core;

public sealed class WindowInput
{
  private const uint WmLbuttonDown = 0x0201;
  private const uint WmLbuttonUp = 0x0202;
  private const uint WmMouseMove = 0x0200;
  private const int MouseeventfLeftDown = 0x0002;
  private const int MouseeventfLeftUp = 0x0004;
  private const int MouseeventfMove = 0x0001;
  private const int MouseeventfAbsolute = 0x8000;

  private readonly IntPtr _hwnd;
  private readonly Action<string>? _log;
  private readonly bool _usePhysicalCursor;

  [DllImport("user32.dll")]
  private static extern bool PostMessage(IntPtr hWnd, uint msg, IntPtr wParam, IntPtr lParam);

  [DllImport("user32.dll")]
  private static extern bool ClientToScreen(IntPtr hWnd, ref Point lpPoint);

  [DllImport("user32.dll")]
  private static extern bool GetClientRect(IntPtr hWnd, out Rect lpRect);

  [DllImport("user32.dll")]
  private static extern bool IsWindow(IntPtr hWnd);

  [DllImport("user32.dll")]
  private static extern bool SetForegroundWindow(IntPtr hWnd);

  [DllImport("user32.dll")]
  private static extern void mouse_event(int dwFlags, int dx, int dy, int dwData, int dwExtraInfo);

  [StructLayout(LayoutKind.Sequential)]
  private struct Point
  {
    public int X;
    public int Y;
  }

  [StructLayout(LayoutKind.Sequential)]
  private struct Rect
  {
    public int Left, Top, Right, Bottom;
  }

  public WindowInput(IntPtr hwnd, Action<string>? log = null, bool usePhysicalCursor = true)
  {
    if (hwnd == IntPtr.Zero || !IsWindow(hwnd))
      throw new InvalidOperationException("Invalid LDPlayer bind window handle");
    _hwnd = hwnd;
    _log = log;
    _usePhysicalCursor = usePhysicalCursor;
  }

  public static WindowInput? TryCreate(LdPlayer ld, Action<string>? log = null)
  {
    var info = ld.GetInstanceInfo();
    if (info is null || info.BindHwnd == 0) return null;
    try
    {
      return new WindowInput(new IntPtr(info.BindHwnd), log);
    }
    catch
    {
      return null;
    }
  }

  private static IntPtr MakeLParam(int x, int y) => (IntPtr)((y << 16) | (x & 0xFFFF));

  private Point ToScreen(int x, int y)
  {
    var pt = new Point { X = x, Y = y };
    ClientToScreen(_hwnd, ref pt);
    return pt;
  }

  public void Tap(int x, int y)
  {
    _log?.Invoke($"win_tap: {x},{y}");
    SetForegroundWindow(_hwnd);
    Thread.Sleep(80);

    if (_usePhysicalCursor)
    {
      var screen = ToScreen(x, y);
      System.Windows.Forms.Cursor.Position = new System.Drawing.Point(screen.X, screen.Y);
      Thread.Sleep(60);
      mouse_event(MouseeventfLeftDown, 0, 0, 0, 0);
      Thread.Sleep(40);
      mouse_event(MouseeventfLeftUp, 0, 0, 0, 0);
    }
    else
    {
      PostMessage(_hwnd, WmLbuttonDown, (IntPtr)1, MakeLParam(x, y));
      Thread.Sleep(50);
      PostMessage(_hwnd, WmLbuttonUp, IntPtr.Zero, MakeLParam(x, y));
    }
    Thread.Sleep(350);
  }

  public void Swipe(int x1, int y1, int x2, int y2, int steps = 12, int stepMs = 25)
  {
    _log?.Invoke($"win_swipe: {x1},{y1} -> {x2},{y2}");
    SetForegroundWindow(_hwnd);
    Thread.Sleep(80);

    if (_usePhysicalCursor)
    {
      var start = ToScreen(x1, y1);
      System.Windows.Forms.Cursor.Position = new System.Drawing.Point(start.X, start.Y);
      Thread.Sleep(60);
      mouse_event(MouseeventfLeftDown, 0, 0, 0, 0);
      for (var i = 1; i <= steps; i++)
      {
        var t = (double)i / steps;
        var cx = (int)(x1 + (x2 - x1) * t);
        var cy = (int)(y1 + (y2 - y1) * t);
        var s = ToScreen(cx, cy);
        System.Windows.Forms.Cursor.Position = new System.Drawing.Point(s.X, s.Y);
        Thread.Sleep(stepMs);
      }
      mouse_event(MouseeventfLeftUp, 0, 0, 0, 0);
    }
    else
    {
      PostMessage(_hwnd, WmLbuttonDown, (IntPtr)1, MakeLParam(x1, y1));
      for (var i = 1; i <= steps; i++)
      {
        var t = (double)i / steps;
        var cx = (int)(x1 + (x2 - x1) * t);
        var cy = (int)(y1 + (y2 - y1) * t);
        PostMessage(_hwnd, WmMouseMove, (IntPtr)1, MakeLParam(cx, cy));
        Thread.Sleep(stepMs);
      }
      PostMessage(_hwnd, WmLbuttonUp, IntPtr.Zero, MakeLParam(x2, y2));
    }
    Thread.Sleep(200);
  }

  public (int w, int h) ClientSize()
  {
    if (!GetClientRect(_hwnd, out var rect))
      return (0, 0);
    return (rect.Right - rect.Left, rect.Bottom - rect.Top);
  }
}
