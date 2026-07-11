using System.Text.Json;
using System.Text.Json.Serialization;

namespace AmberHarbor.Core;

public static class FleetRegistry
{
  private static readonly JsonSerializerOptions JsonOpts = new()
  {
    WriteIndented = true,
    PropertyNamingPolicy = JsonNamingPolicy.CamelCase,
    DefaultIgnoreCondition = JsonIgnoreCondition.WhenWritingNull,
  };

  public static string FleetFile => Path.Combine(FarmPaths.ProjectRoot, "data", "fleet.json");

  public static FleetDocument Load()
  {
    FarmPaths.EnsureDataDirs();
    if (!File.Exists(FleetFile))
      return new FleetDocument();
    var json = File.ReadAllText(FleetFile);
    return JsonSerializer.Deserialize<FleetDocument>(json, JsonOpts) ?? new FleetDocument();
  }

  public static void Save(FleetDocument doc)
  {
    FarmPaths.EnsureDataDirs();
    File.WriteAllText(FleetFile, JsonSerializer.Serialize(doc, JsonOpts));
  }

  public static FleetInstance GetOrCreate(FleetDocument doc, int index)
  {
    var inst = doc.Instances.FirstOrDefault(i => i.Index == index);
    if (inst is not null) return inst;
    inst = new FleetInstance
    {
      Index = index,
      Name = FleetIndex.InstanceName(index),
      AdbPort = FleetIndex.AdbPort(index),
    };
    doc.Instances.Add(inst);
    return inst;
  }

  public static void RecordLocation(FleetDocument doc, int index, string? ip, string? country, string? city)
  {
    var inst = GetOrCreate(doc, index);
    inst.LastIp = ip;
    inst.LastCountry = country;
    inst.LastCity = city;
    inst.LocationVerifiedAt = DateTimeOffset.UtcNow;
    Save(doc);
  }

  public static void RecordVpnOk(FleetDocument doc, int index, bool permissionGranted = false)
  {
    var inst = GetOrCreate(doc, index);
    inst.VpnOkAt = DateTimeOffset.UtcNow;
    if (permissionGranted) inst.VpnPermissionGranted = true;
    Save(doc);
  }
}
