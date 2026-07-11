namespace AmberHarbor.Flows;

public static class FleetInitFlow
{
  public static int Run(FarmContext ctx, int count)
  {
    var fleet = ctx.Settings.Fleet;
    var template = fleet.TemplateIndex;
    Console.WriteLine($"fleet init: template={template} count={count}");

    if (count < 1)
    {
      Console.WriteLine("fleet init: count must be >= 1");
      return 1;
    }

    var templateLd = new Core.LdPlayer(ctx.Settings, ctx.Log, template);
    var existing = templateLd.ListInstances();
    if (!existing.Any(i => i.Index == template))
    {
      Console.WriteLine($"fleet init: template index {template} not found in ldconsole list2");
      return 1;
    }

    var doc = Core.FleetRegistry.Load();
    doc.TemplateIndex = template;
    doc.Version = 1;

    for (var i = 0; i < count; i++)
    {
      existing = templateLd.ListInstances();
      if (!existing.Any(x => x.Index == i))
      {
        if (i == template)
        {
          Console.WriteLine($"fleet init: template index {template} missing");
          return 1;
        }
        var name = Core.FleetIndex.InstanceName(i);
        Console.WriteLine($"fleet init: copy --from {template} → index {i} name={name}");
        templateLd.CopyFrom(template, name);
        Thread.Sleep(10000);
        existing = templateLd.ListInstances();
        if (!existing.Any(x => x.Index == i))
        {
          Console.WriteLine($"fleet init: copy did not produce index {i} — check LDPlayer multi-instance limit");
          return 1;
        }
      }

      var ld = new Core.LdPlayer(ctx.Settings, ctx.Log, i);
      ld.ApplyFleetProfile(fleet);
      if (Core.LdConfig.EnsureAdbEnabled(ld.Home, i, ctx.Log))
        Console.WriteLine($"fleet init: adbDebug patched index={i}");
      else
        Console.WriteLine($"fleet init: warn adbDebug patch failed index={i}");

      var inst = Core.FleetRegistry.GetOrCreate(doc, i);
      inst.Name = existing.First(x => x.Index == i).Name;
      inst.AdbPort = Core.FleetIndex.AdbPort(i);
    }

    doc.Instances = doc.Instances
      .Where(i => i.Index < count)
      .OrderBy(i => i.Index)
      .ToList();
    fleet.InstanceCount = count;
    Core.FleetRegistry.Save(doc);

    Console.WriteLine($"fleet init: ok ({count} instances → {Core.FleetRegistry.FleetFile})");
    return 0;
  }
}
