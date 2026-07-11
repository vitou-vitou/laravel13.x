namespace AmberHarbor.Flows;

public static class SetupFlow
{
  public static int Run(FarmContext ctx, bool skipVpn = false)
  {
    ctx.Log?.Invoke($"==> setup index={ctx.Index}");
    ctx.Ld.Launch();
    Thread.Sleep(5000);

    if (!ctx.Adb.EnsureReady(ctx.Settings))
    {
      Console.WriteLine("setup: adb_unavailable");
      return 1;
    }

    var apps = new Core.AppProvisioner(ctx.Settings, ctx.Ld, ctx.Adb, ctx.Ui, ctx.Log);

    if (!apps.EnsureSurfshark())
    {
      Console.WriteLine("setup: surfshark_install_failed — add APK to assets/apks/ or sign into Play Store in LDPlayer");
      ctx.Ui.ScreenshotStep("setup-surfshark-fail", ctx.Ld);
      return 1;
    }

    if (!apps.EnsureTikTok())
    {
      Console.WriteLine("setup: tiktok_install_failed");
      ctx.Ui.ScreenshotStep("setup-tiktok-fail", ctx.Ld);
      return 1;
    }

    if (!skipVpn)
    {
      var vpn = ctx.VpnOrchestrator();
      if (vpn.NeedsManualLogin())
      {
        Console.WriteLine("setup: open Surfshark in LDPlayer, log in, then run: setup --index N");
        ctx.Ui.ScreenshotStep("setup-surfshark-login", ctx.Ld);
        return 1;
      }

      if (!vpn.EnsureConnected(ctx.Vpn.TargetCountry))
      {
        Console.WriteLine("setup: vpn_usa_failed");
        ctx.Ui.ScreenshotStep("setup-vpn-fail", ctx.Ld);
        return 1;
      }

      Core.FleetRegistry.RecordVpnOk(Core.FleetRegistry.Load(), ctx.Index, permissionGranted: true);

      var loc = ctx.LocationVerifier().Verify(ctx.Vpn.TargetCountry);
      if (!loc.Ok)
      {
        Console.WriteLine($"setup: location_not_us ip={loc.Ip} country={loc.Country}");
        return 1;
      }

      Core.FleetRegistry.RecordLocation(
        Core.FleetRegistry.Load(), ctx.Index, loc.Ip, loc.Country, loc.City);
      Console.WriteLine($"setup: vpn usa ok ip={loc.Ip}");
    }

    Core.LdConfig.EnsureAdbEnabled(ctx.Ld.Home, ctx.Index, ctx.Log);
    Console.WriteLine("setup: ok (Surfshark + TikTok + USA VPN + adbDebug)");
    return 0;
  }
}
