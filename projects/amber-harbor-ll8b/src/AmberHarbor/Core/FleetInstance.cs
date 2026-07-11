using System.Text.Json.Serialization;

namespace AmberHarbor.Core;

public sealed class FleetInstance
{
  [JsonPropertyName("index")] public int Index { get; set; }
  [JsonPropertyName("name")] public string Name { get; set; } = "";
  [JsonPropertyName("adbPort")] public int AdbPort { get; set; }
  [JsonPropertyName("lastIp")] public string? LastIp { get; set; }
  [JsonPropertyName("lastCountry")] public string? LastCountry { get; set; }
  [JsonPropertyName("lastCity")] public string? LastCity { get; set; }
  [JsonPropertyName("vpnOkAt")] public DateTimeOffset? VpnOkAt { get; set; }
  [JsonPropertyName("vpnPermissionGranted")] public bool VpnPermissionGranted { get; set; }
  [JsonPropertyName("locationVerifiedAt")] public DateTimeOffset? LocationVerifiedAt { get; set; }
}

public sealed class FleetDocument
{
  [JsonPropertyName("version")] public int Version { get; set; } = 1;
  [JsonPropertyName("templateIndex")] public int TemplateIndex { get; set; }
  [JsonPropertyName("instances")] public List<FleetInstance> Instances { get; set; } = [];
}
