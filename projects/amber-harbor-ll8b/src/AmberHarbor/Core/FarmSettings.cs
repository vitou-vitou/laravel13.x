using System.Text.Json;
using System.Text.Json.Serialization;

namespace AmberHarbor.Core;

public sealed class FarmSettings
{
  [JsonPropertyName("email")] public string EmailUser { get; set; } = "";
  [JsonPropertyName("eMailEnd")] public string EmailDomain { get; set; } = "gmail.com";
  [JsonPropertyName("gmailPass")] public string GmailPass { get; set; } = "";
  [JsonPropertyName("password")] public string Password { get; set; } = "";
  [JsonPropertyName("maxAccountsPerDay")] public int MaxAccountsPerDay { get; set; } = 3;
  [JsonPropertyName("ldplayerHome")] public string LdPlayerHome { get; set; } = @"D:\LDPlayer\LDPlayer9";
  [JsonPropertyName("ldplayerIndex")] public int LdPlayerIndex { get; set; }
  [JsonPropertyName("tiktokPackage")] public string TikTokPackage { get; set; } = "com.zhiliaoapp.musically";
  [JsonPropertyName("fleet")] public FleetSettings Fleet { get; set; } = new();
  [JsonPropertyName("apps")] public AppInstallSettings Apps { get; set; } = new();

  public string FullGmailAddress => $"{EmailUser}@{EmailDomain}";

  public string AliasEmail(string suffix = "farm")
  {
    var stamp = (DateTimeOffset.UtcNow.ToUnixTimeSeconds() % 100000).ToString();
    return $"{EmailUser}+{suffix}{stamp}@{EmailDomain}";
  }

  public static FarmSettings Load()
  {
    var path = FarmPaths.SettingsFile;
    if (!File.Exists(path))
      throw new FileNotFoundException($"Missing {path} — copy settings.example.json to settings.json");
    var json = File.ReadAllText(path);
    return JsonSerializer.Deserialize<FarmSettings>(json, JsonOpts) ?? new FarmSettings();
  }

  private static readonly JsonSerializerOptions JsonOpts = new()
  {
    PropertyNameCaseInsensitive = true,
    ReadCommentHandling = JsonCommentHandling.Skip,
    AllowTrailingCommas = true,
  };
}
