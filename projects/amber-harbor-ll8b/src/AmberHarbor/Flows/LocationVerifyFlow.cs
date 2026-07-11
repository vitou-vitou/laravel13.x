namespace AmberHarbor.Flows;

public static class LocationVerifyFlow
{
  public static int Run(FarmContext ctx)
  {
    ctx.Log?.Invoke($"==> location verify index={ctx.Index}");
    ctx.Ld.Launch();
    Thread.Sleep(3000);
    if (!ctx.Adb.EnsureReady(ctx.Settings))
    {
      Console.WriteLine("location verify: adb_unavailable");
      return 1;
    }

    var result = ctx.LocationVerifier().Verify(ctx.Vpn.TargetCountry);
    if (!result.Ok)
    {
      Console.WriteLine($"location verify: failed {result.Error} (country={result.Country})");
      return 1;
    }

    Core.FleetRegistry.RecordLocation(
      Core.FleetRegistry.Load(), ctx.Index, result.Ip, result.Country, result.City);
    Console.WriteLine($"location verify: ok ip={result.Ip} country={result.Country} city={result.City}");
    return 0;
  }
}
