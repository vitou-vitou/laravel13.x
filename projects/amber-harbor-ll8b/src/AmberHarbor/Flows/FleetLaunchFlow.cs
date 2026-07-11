namespace AmberHarbor.Flows;

public static class FleetLaunchFlow
{
  public static int Run(FarmContext ctx, int? count = null, int? startIndex = null)
  {
    var fleet = ctx.Settings.Fleet;
    var total = count ?? fleet.InstanceCount;
    var start = startIndex ?? 0;
    var stagger = Math.Max(0, fleet.LaunchStaggerSeconds);

    Console.WriteLine($"fleet launch: indices {start}..{start + total - 1} stagger={stagger}s");
    for (var i = start; i < start + total; i++)
    {
      var ld = new Core.LdPlayer(ctx.Settings, ctx.Log, i);
      if (!ld.InstanceExists())
      {
        Console.WriteLine($"fleet launch: skip missing index={i}");
        continue;
      }
      ld.Launch();
      if (i < start + total - 1 && stagger > 0)
        Thread.Sleep(stagger * 1000);
    }

    Console.WriteLine("fleet launch: ok");
    return 0;
  }
}
