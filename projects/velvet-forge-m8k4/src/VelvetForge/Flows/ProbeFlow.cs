namespace VelvetForge.Flows;

public static class ProbeFlow
{
  public static int Run(FarmContext ctx)
  {
    ctx.Log?.Invoke("==> probe");
    ctx.Ld.Launch();
    Thread.Sleep(3000);

    var info = ctx.Ld.GetInstanceInfo();
    if (info is null)
    {
      Console.WriteLine("probe: no instance");
      return 1;
    }

    ctx.Adb.Connect();
    ctx.Log?.Invoke($"adb ready: {ctx.Adb.DeviceReady()} port {ctx.Ld.ResolveAdbPort()}");
    ctx.Log?.Invoke($"input backend: {ctx.Input.ActiveBackend}");
    ctx.Log?.Invoke($"bind hwnd: {info.BindHwnd} size {info.Width}x{info.Height}");

    ctx.Ld.RunApp();
    Thread.Sleep(6000);
    ctx.Screenshot("probe-mlbb");

    if (ctx.Ui.Available)
    {
      ctx.Ui.Dump();
      var hasClassic = ctx.Ui.HasText("Classic");
      var hasHero = ctx.Ui.HasText("Hero");
      ctx.Log?.Invoke($"ui: Classic={hasClassic} Hero={hasHero}");
      Console.WriteLine($"probe: adb={ctx.Adb.IsReady} backend={ctx.Input.ActiveBackend} classic={hasClassic}");
      return 0;
    }

    Console.WriteLine($"probe: backend={ctx.Input.ActiveBackend} (no uiautomator — coordinate mode)");
    return 0;
  }
}
