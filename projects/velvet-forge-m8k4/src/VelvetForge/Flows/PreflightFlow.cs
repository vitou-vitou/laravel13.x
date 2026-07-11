namespace VelvetForge.Flows;

public static class PreflightFlow
{
  public static int Run(FarmContext ctx)
  {
    ctx.Log?.Invoke("==> preflight");
    ctx.Ld.Launch();
    Thread.Sleep(5000);

    var info = ctx.Ld.GetInstanceInfo();
    if (info is null)
    {
      Console.WriteLine("preflight: ldplayer_instance_missing");
      return 1;
    }
    ctx.Log?.Invoke($"ldplayer: {info.Width}x{info.Height} bindHwnd={info.BindHwnd} android={info.AndroidStarted}");

    var forceWindow = string.Equals(ctx.Settings.InputBackend, "window", StringComparison.OrdinalIgnoreCase);
    var adbOk = !forceWindow && ctx.Settings.PreferAdb && ctx.Adb.EnsureReady(ctx.Settings);
    if (!adbOk)
      ctx.Log?.Invoke(forceWindow ? "preflight: window input mode" : "preflight: adb unavailable — window input fallback");

    if (info.BindHwnd == 0)
    {
      Console.WriteLine("preflight: bind_window_missing");
      return 1;
    }

    var pkg = ctx.Settings.MlbbPackage;
    var installed = adbOk && ctx.Ld.IsPackageInstalled(pkg);
    if (adbOk && !installed)
    {
      Console.WriteLine($"preflight: mlbb_not_installed ({pkg}) — install via LDPlayer Play Store");
      ctx.Screenshot("preflight-no-mlbb");
      return 1;
    }

    ctx.Ld.RunApp();
    Thread.Sleep(8000);
    ctx.Ui.DismissPopups(ctx.Input);
    ctx.Screenshot("preflight-mlbb");

    if (ctx.Ui.Available && (ctx.Ui.HasText("Mobile Legends") || ctx.Ui.HasText("Classic") || ctx.Ui.HasText("MOONTON")))
    {
      Console.WriteLine("preflight: ok");
      return 0;
    }

    ctx.Screenshot("preflight-final");
    Console.WriteLine(installed || !adbOk
      ? "preflight: ok (window backend — verify MLBB visible in screenshot)"
      : "preflight: mlbb_not_detected");
    return installed || !adbOk ? 0 : 1;
  }
}
