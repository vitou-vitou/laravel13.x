using VelvetForge.Core;
using VelvetForge.Mlbb;

namespace VelvetForge.Flows;

public sealed class FarmContext
{
  public required FarmSettings Settings { get; init; }
  public required LdPlayer Ld { get; init; }
  public required AdbClient Adb { get; init; }
  public required InputBridge Input { get; init; }
  public required UiAutomator Ui { get; init; }
  public Action<string>? Log { get; init; }

  public static FarmContext Create(Action<string>? log = null)
  {
    var settings = FarmSettings.Load();
    var ld = new LdPlayer(settings, log);
    ld.Launch();
    Thread.Sleep(2000);
    SyncScreenSize(settings, ld, log);

    var adb = new AdbClient(ld, log);
    var input = InputBridge.Create(settings, ld, adb, log);
    var ui = new UiAutomator(adb, log);
    return new FarmContext
    {
      Settings = settings,
      Ld = ld,
      Adb = adb,
      Input = input,
      Ui = ui,
      Log = log,
    };
  }

  static void SyncScreenSize(FarmSettings settings, LdPlayer ld, Action<string>? log)
  {
    var info = ld.GetInstanceInfo();
    if (info is null || info.Width <= 0 || info.Height <= 0) return;
    settings.ScreenWidth = info.Width;
    settings.ScreenHeight = info.Height;
    log?.Invoke($"screen: {info.Width}x{info.Height} (from list2)");
  }

  public void Screenshot(string name)
  {
    var path = Path.Combine(FarmPaths.ScreenshotDir, $"{name}-{DateTimeOffset.UtcNow.ToUnixTimeSeconds()}.png");
    if (ScreenCapture.CaptureBindWindow(Ld, path))
      Log?.Invoke($"screenshot: {path}");
    else if (Ld.Screenshot(path))
      Log?.Invoke($"screenshot: {path}");
  }

  public void SyncScreen()
  {
    var info = Ld.GetInstanceInfo();
    if (info is null || info.Width <= 0 || info.Height <= 0) return;
    if (Settings.ScreenWidth == info.Width && Settings.ScreenHeight == info.Height) return;
    Settings.ScreenWidth = info.Width;
    Settings.ScreenHeight = info.Height;
    Log?.Invoke($"screen sync: {info.Width}x{info.Height}");
  }
}
