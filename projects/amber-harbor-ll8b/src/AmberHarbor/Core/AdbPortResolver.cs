namespace AmberHarbor.Core;

public static class AdbPortResolver
{
  public static int ReadConfigPort(string ldHome, int fallback = 5555)
  {
    var cfg = Path.Combine(ldHome, "config", "ld.config");
    if (!File.Exists(cfg)) return fallback;
    foreach (var line in File.ReadAllLines(cfg))
    {
      if (line.StartsWith("adb_port=", StringComparison.OrdinalIgnoreCase))
      {
        var val = line["adb_port=".Length..].Trim();
        if (int.TryParse(val, out var port)) return port;
      }
    }
    return fallback;
  }

  public static IEnumerable<int> CandidatePorts(string ldHome, int emulatorIndex)
  {
    var seen = new HashSet<int>();
    var list = new List<int>();
    void Track(int p)
    {
      if (p > 0 && seen.Add(p)) list.Add(p);
    }

    Track(5555 + emulatorIndex * 2);
    Track(ReadConfigPort(ldHome));
    Track(5557 + emulatorIndex * 2);
    for (var p = 5555; p <= 5585; p++) Track(p);
    return list;
  }
}
