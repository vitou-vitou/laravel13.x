namespace VelvetForge.Core;

public enum InputBackendKind
{
  Auto,
  Adb,
  Window,
}

public sealed class InputBridge
{
  private readonly AdbClient? _adb;
  private readonly WindowInput? _window;
  private readonly FarmSettings _settings;
  private readonly Action<string>? _log;
  public InputBackendKind ActiveBackend { get; private set; }

  public InputBridge(FarmSettings settings, AdbClient adb, WindowInput? window, Action<string>? log = null)
  {
    _settings = settings;
    _adb = adb;
    _window = window;
    _log = log;
    ActiveBackend = ResolveBackend(settings.InputBackend, adb.IsReady, window is not null);
    _log?.Invoke($"input: using {ActiveBackend}");
  }

  public static InputBridge Create(FarmSettings settings, LdPlayer ld, AdbClient adb, Action<string>? log = null)
  {
    WindowInput? win = null;
    var info = ld.GetInstanceInfo();
    if (info is not null && info.BindHwnd != 0)
      win = WindowInput.TryCreate(ld, log);

    var preferAdb = settings.PreferAdb &&
                    !string.Equals(settings.InputBackend, "window", StringComparison.OrdinalIgnoreCase);
    if (preferAdb && !adb.IsReady &&
        !string.Equals(settings.InputBackend, "window", StringComparison.OrdinalIgnoreCase))
    {
      adb.Connect();
      if (!adb.DeviceReady())
        log?.Invoke("adb: quick connect failed — using window fallback if available");
    }

    return new InputBridge(settings, adb, win, log);
  }

  private static InputBackendKind ResolveBackend(string configured, bool adbReady, bool windowReady)
  {
    if (string.Equals(configured, "adb", StringComparison.OrdinalIgnoreCase))
      return adbReady ? InputBackendKind.Adb : InputBackendKind.Window;
    if (string.Equals(configured, "window", StringComparison.OrdinalIgnoreCase))
      return InputBackendKind.Window;

    if (adbReady) return InputBackendKind.Adb;
    if (windowReady) return InputBackendKind.Window;
    return InputBackendKind.Window;
  }

  public void Tap(int x, int y)
  {
    if (ActiveBackend == InputBackendKind.Adb && _adb?.IsReady == true)
    {
      _adb.Tap(x, y);
      Thread.Sleep(650);
      return;
    }
    if (_window is null)
      throw new InvalidOperationException("No input backend available (ADB off, window handle missing)");
    _window.Tap(x, y);
  }

  public void Swipe(int x1, int y1, int x2, int y2, int dur = 300)
  {
    if (ActiveBackend == InputBackendKind.Adb && _adb?.IsReady == true)
    {
      _adb.Swipe(x1, y1, x2, y2, dur);
      Thread.Sleep(200);
      return;
    }
    _window?.Swipe(x1, y1, x2, y2);
  }

  public void Text(string text)
  {
    if (ActiveBackend == InputBackendKind.Adb && _adb?.IsReady == true)
    {
      _adb.Text(text);
      return;
    }
    foreach (var ch in text)
    {
      if (ch == ' ')
        TapScale(0.5, 0.95);
      else
        _adb?.Key(KeyCodeForChar(ch));
      Thread.Sleep(80);
    }
  }

  private static int KeyCodeForChar(char ch)
  {
    if (char.IsLetterOrDigit(ch))
      return ch is >= 'a' and <= 'z' ? 29 + (ch - 'a') : 7 + (char.ToUpper(ch) - 'A');
    return 62;
  }

  public (int x, int y) Scale(double rx, double ry)
  {
    var w = _settings.ScreenWidth;
    var h = _settings.ScreenHeight;
    return ((int)(w * rx), (int)(h * ry));
  }

  public void TapScale(double rx, double ry)
  {
    var (x, y) = Scale(rx, ry);
    Tap(x, y);
  }

  public void SwipeScale(double rx1, double ry1, double rx2, double ry2) =>
    Swipe(Scale(rx1, ry1).x, Scale(rx1, ry1).y, Scale(rx2, ry2).x, Scale(rx2, ry2).y);

  public void Back()
  {
    if (ActiveBackend == InputBackendKind.Adb && _adb?.IsReady == true)
    {
      _adb.Back();
      return;
    }
    TapScale(0.04, 0.50);
  }
}
