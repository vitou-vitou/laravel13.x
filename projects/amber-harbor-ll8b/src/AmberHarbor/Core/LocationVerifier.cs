using System.Text.Json;

namespace AmberHarbor.Core;

public sealed record LocationResult(bool Ok, string? Ip, string? Country, string? City, string? Raw, string? Error)
{
  public static LocationResult Fail(string error) => new(false, null, null, null, null, error);
}

public sealed class LocationVerifier(AdbClient adb, VpnSettings vpn, Action<string>? log = null)
{
  private void Log(string msg)
  {
    log?.Invoke(msg);
    Console.WriteLine(msg);
  }

  public LocationResult Verify(string expectedCountry = "US")
  {
    var url = vpn.VerifyUrl;
    Log($"location: fetching {url}");
    var raw = FetchJson(url);
    if (string.IsNullOrWhiteSpace(raw))
      return LocationResult.Fail("empty_response");

    try
    {
      using var doc = JsonDocument.Parse(raw);
      var root = doc.RootElement;
      var ip = root.TryGetProperty("ip", out var ipEl) ? ipEl.GetString() : null;
      var country = root.TryGetProperty("country", out var cEl) ? cEl.GetString() : null;
      var city = root.TryGetProperty("city", out var cityEl) ? cityEl.GetString() : null;
      var ok = string.Equals(country, expectedCountry, StringComparison.OrdinalIgnoreCase);
      Log($"location: ip={ip} country={country} city={city} expected={expectedCountry} ok={ok}");
      return new LocationResult(ok, ip, country, city, raw, ok ? null : "country_mismatch");
    }
    catch (Exception ex)
    {
      return LocationResult.Fail($"parse_error: {ex.Message}");
    }
  }

  private string FetchJson(string url)
  {
    var escaped = url.Replace("'", "'\\''");
    var commands = new[]
    {
      $"curl -s --max-time 15 '{escaped}'",
      $"curl -s --max-time 15 {escaped}",
      $"wget -qO- --timeout=15 '{escaped}'",
    };
    foreach (var cmd in commands)
    {
      var outText = adb.Shell(cmd).Trim();
      if (outText.Contains('{') && outText.Contains('}'))
        return ExtractJson(outText);
    }
    return "";
  }

  private static string ExtractJson(string text)
  {
    var start = text.IndexOf('{');
    var end = text.LastIndexOf('}');
    if (start < 0 || end <= start) return text;
    return text[start..(end + 1)];
  }
}
