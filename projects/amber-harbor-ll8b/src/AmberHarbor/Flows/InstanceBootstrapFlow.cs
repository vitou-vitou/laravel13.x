namespace AmberHarbor.Flows;

public static class InstanceBootstrapFlow
{
  public static bool Ensure(FarmContext ctx, bool requireVpn = true, bool requireLocation = true)
  {
    ctx.Log?.Invoke($"==> instance bootstrap index={ctx.Index}");
    if (!ctx.Adb.EnsureReady(ctx.Settings))
    {
      Console.WriteLine("bootstrap: adb_unavailable");
      return false;
    }

    if (!requireVpn && !requireLocation)
      return true;

    var vpn = ctx.VpnOrchestrator();
    if (requireVpn)
    {
      if (!vpn.IsPackageInstalled())
      {
        Console.WriteLine($"bootstrap: surfshark_not_installed ({ctx.Vpn.Package}) — complete Phase 0 on template index {ctx.Settings.Fleet.TemplateIndex}");
        return false;
      }

      if (!vpn.EnsureConnected(ctx.Vpn.TargetCountry))
      {
        Console.WriteLine("bootstrap: vpn_not_connected");
        return false;
      }

      Core.FleetRegistry.RecordVpnOk(Core.FleetRegistry.Load(), ctx.Index);
    }

    if (requireLocation)
    {
      var loc = ctx.LocationVerifier().Verify(ctx.Vpn.TargetCountry);
      if (!loc.Ok)
      {
        Console.WriteLine($"bootstrap: location_failed {loc.Error} country={loc.Country}");
        return false;
      }

      Core.FleetRegistry.RecordLocation(
        Core.FleetRegistry.Load(), ctx.Index, loc.Ip, loc.Country, loc.City);
    }

    Console.WriteLine("bootstrap: ok");
    return true;
  }
}
