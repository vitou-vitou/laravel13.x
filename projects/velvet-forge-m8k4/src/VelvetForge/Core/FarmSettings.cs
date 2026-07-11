using System.Text.Json;
using System.Text.Json.Serialization;

namespace VelvetForge.Core;

public sealed class FarmSettings
{
  [JsonPropertyName("ldplayerHome")] public string LdPlayerHome { get; set; } = @"D:\LDPlayer\LDPlayer9";
  [JsonPropertyName("ldplayerIndex")] public int LdPlayerIndex { get; set; }
  [JsonPropertyName("mlbbPackage")] public string MlbbPackage { get; set; } = "com.mobile.legends";
  [JsonPropertyName("heroName")] public string HeroName { get; set; } = "Layla";
  [JsonPropertyName("gameMode")] public string GameMode { get; set; } = "classic";
  [JsonPropertyName("playDurationSeconds")] public int PlayDurationSeconds { get; set; } = 480;
  [JsonPropertyName("screenWidth")] public int ScreenWidth { get; set; } = 1600;
  [JsonPropertyName("screenHeight")] public int ScreenHeight { get; set; } = 900;
  [JsonPropertyName("preferAdb")] public bool PreferAdb { get; set; } = true;
  [JsonPropertyName("inputBackend")] public string InputBackend { get; set; } = "auto";

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
