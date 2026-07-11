namespace AmberHarbor.Flows;

public static class FleetStatusFlow
{
  public static int Run(FarmContext ctx)
  {
    var doc = Core.FleetRegistry.Load();
    var live = ctx.Ld.ListInstances();
    Console.WriteLine($"fleet status: template={doc.TemplateIndex} registered={doc.Instances.Count}");
    Console.WriteLine("index  name        running  adb_port  country  ip  vpn_ok");
    foreach (var inst in doc.Instances.OrderBy(i => i.Index))
    {
      var ld = new Core.LdPlayer(ctx.Settings, ctx.Log, inst.Index);
      var running = ld.IsRunning() ? "yes" : "no";
      var country = inst.LastCountry ?? "-";
      var ip = inst.LastIp ?? "-";
      var vpn = inst.VpnOkAt.HasValue ? inst.VpnOkAt.Value.ToString("u") : "-";
      Console.WriteLine($"{inst.Index,5}  {inst.Name,-10}  {running,-7}  {inst.AdbPort,8}  {country,-7}  {ip,-15}  {vpn}");
    }

    var missing = live.Select(l => l.Index).Except(doc.Instances.Select(i => i.Index)).ToList();
    if (missing.Count > 0)
      Console.WriteLine($"fleet status: ldconsole instances not in registry: {string.Join(", ", missing)}");

    return 0;
  }
}
