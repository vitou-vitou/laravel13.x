using System.Text.Json.Serialization;

namespace AmberHarbor.Core;

public sealed class AppInstallSettings
{
  [JsonPropertyName("surfsharkApk")] public string SurfsharkApk { get; set; } = "";
  [JsonPropertyName("tiktokApk")] public string TikTokApk { get; set; } = "";
  [JsonPropertyName("playStorePackage")] public string PlayStorePackage { get; set; } = "com.android.vending";
  [JsonPropertyName("installTimeoutSeconds")] public int InstallTimeoutSeconds { get; set; } = 180;
}
