using System.Text.Json;
using System.Text.Json.Nodes;

namespace AmberHarbor.Core;

/// <summary>Patches vms/config/leidian{N}.config for ADB + root without UI automation.</summary>
public static class LdConfig
{
  public static string ConfigPath(string ldHome, int index) =>
    Path.Combine(ldHome, "vms", "config", $"leidian{index}.config");

  public static bool EnsureAdbEnabled(string ldHome, int index, Action<string>? log = null)
  {
    var path = ConfigPath(ldHome, index);
    if (!File.Exists(path))
    {
      log?.Invoke($"ldconfig: missing {path}");
      return false;
    }

    var json = File.ReadAllText(path);
    JsonNode? root;
    try
    {
      root = JsonNode.Parse(json);
    }
    catch (Exception ex)
    {
      log?.Invoke($"ldconfig: parse error — {ex.Message}");
      return false;
    }

    if (root is not JsonObject obj) return false;

    var rootMode = obj["basicSettings.rootMode"]?.GetValue<bool?>() ?? false;
    var adbDebug = obj["basicSettings.adbDebug"]?.GetValue<int?>() ?? 0;

    if (rootMode && adbDebug == 1)
    {
      log?.Invoke("ldconfig: adbDebug already enabled");
      return true;
    }

    obj["basicSettings.rootMode"] = true;
    obj["basicSettings.adbDebug"] = 1;

    var opts = new JsonSerializerOptions { WriteIndented = true };
    File.WriteAllText(path, obj.ToJsonString(opts));
    log?.Invoke("ldconfig: set rootMode=true, adbDebug=1 — reboot required");
    return true;
  }
}
