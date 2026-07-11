using System.Text.Json.Serialization;

namespace AmberHarbor.Core;

public sealed class FleetSettings
{
  [JsonPropertyName("templateIndex")] public int TemplateIndex { get; set; }
  [JsonPropertyName("instanceCount")] public int InstanceCount { get; set; } = 1;
  [JsonPropertyName("launchStaggerSeconds")] public int LaunchStaggerSeconds { get; set; } = 5;
  [JsonPropertyName("resolution")] public string Resolution { get; set; } = "720,1280,320";
  [JsonPropertyName("memoryMb")] public int MemoryMb { get; set; } = 2048;
  [JsonPropertyName("cpuCount")] public int CpuCount { get; set; } = 2;
  [JsonPropertyName("vpn")] public VpnSettings Vpn { get; set; } = new();
}

public sealed class VpnSettings
{
  [JsonPropertyName("package")] public string Package { get; set; } = "com.surfshark.vpnclient.android";
  [JsonPropertyName("targetCountry")] public string TargetCountry { get; set; } = "US";
  [JsonPropertyName("verifyUrl")] public string VerifyUrl { get; set; } = "https://ipinfo.io/json";
}
