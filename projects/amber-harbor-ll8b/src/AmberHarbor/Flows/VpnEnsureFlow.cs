namespace AmberHarbor.Flows;

public static class VpnEnsureFlow
{
  public static int Run(FarmContext ctx)
  {
    ctx.Log?.Invoke($"==> vpn ensure index={ctx.Index}");
    ctx.Ld.Launch();
    Thread.Sleep(5000);
    if (!ctx.Adb.EnsureReady(ctx.Settings))
    {
      Console.WriteLine("vpn ensure: adb_unavailable");
      return 1;
    }

    var vpn = ctx.VpnOrchestrator();
    if (!vpn.IsPackageInstalled())
    {
      Console.WriteLine($"vpn ensure: package missing {ctx.Vpn.Package}");
      return 1;
    }

    if (!vpn.EnsureConnected(ctx.Vpn.TargetCountry))
    {
      Console.WriteLine("vpn ensure: connect_failed");
      return 1;
    }

    Core.FleetRegistry.RecordVpnOk(Core.FleetRegistry.Load(), ctx.Index, permissionGranted: true);
    Console.WriteLine("vpn ensure: ok");
    return 0;
  }
}
