namespace AmberHarbor.Flows;

public static class PreflightFlow
{
  public static int Run(FarmContext ctx, bool skipVpn = false)
  {
    ctx.Log?.Invoke($"==> preflight index={ctx.Index}");
    ctx.Ld.Launch();
    Thread.Sleep(5000);
    if (!ctx.Adb.EnsureReady(ctx.Settings))
    {
      Console.WriteLine("preflight: adb_unavailable");
      return 1;
    }

    if (!skipVpn && !InstanceBootstrapFlow.Ensure(ctx, requireVpn: true, requireLocation: true))
    {
      Console.WriteLine("preflight: instance_bootstrap_failed (vpn/location) — use vpn ensure / location verify, or --skip-vpn");
      return 1;
    }

    var sample = Core.FarmPaths.SampleVideo;
    if (!File.Exists(sample))
    {
      Console.WriteLine($"preflight: missing_sample {sample}");
      return 1;
    }

    ctx.Ld.RunApp();
    Thread.Sleep(6000);
    ctx.Ui.DismissPopups();

    if (ctx.Ui.HasText("TikTok") || ctx.Ui.HasText("For You") || ctx.Ui.HasText("Profile")
        || ctx.Ui.HasText("Sign up"))
    {
      Console.WriteLine("preflight: ok");
      return 0;
    }

    Console.WriteLine("preflight: tiktok_not_detected (install TikTok in LDPlayer Play Store)");
    return 1;
  }
}
