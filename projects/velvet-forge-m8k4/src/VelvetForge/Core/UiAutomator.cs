using System.Text.RegularExpressions;

namespace VelvetForge.Core;

public sealed class UiAutomator(AdbClient adb, Action<string>? log = null)
{
  private static readonly Regex BoundsRe = new(
    @"bounds=""\[(\d+),(\d+)\]\[(\d+),(\d+)\]""",
    RegexOptions.IgnoreCase | RegexOptions.Compiled);

  private void Log(string msg) => log?.Invoke(msg);

  public bool Available => adb.IsReady;

  public bool Dump()
  {
    if (!adb.IsReady) return false;
    FarmPaths.EnsureDataDirs();
    adb.Shell("uiautomator dump /sdcard/window_dump.xml");
    try
    {
      var xml = adb.Shell("cat /sdcard/window_dump.xml");
      if (xml.Length > 100)
      {
        File.WriteAllText(FarmPaths.UiDumpFile, xml);
        return true;
      }
    }
    catch { /* ignore */ }
    return File.Exists(FarmPaths.UiDumpFile) && new FileInfo(FarmPaths.UiDumpFile).Length > 0;
  }

  public (int x, int y)? FindBounds(string pattern)
  {
    if (!Dump()) return null;
    var xml = File.ReadAllText(FarmPaths.UiDumpFile);
    var nodeRe = new Regex(
      $@"(text|content-desc)=""(?:[^""]*{Regex.Escape(pattern)}[^""]*)""[^>]*bounds=""\[\d+,\d+\]\[\d+,\d+\]""",
      RegexOptions.IgnoreCase);
    var m = nodeRe.Match(xml);
    if (!m.Success)
    {
      nodeRe = new Regex(
        $@"bounds=""\[\d+,\d+\]\[\d+,\d+\]""[^>]*(text|content-desc)=""(?:[^""]*{Regex.Escape(pattern)}[^""]*)""",
        RegexOptions.IgnoreCase);
      m = nodeRe.Match(xml);
    }
    if (!m.Success) return null;

    var boundsMatch = BoundsRe.Match(m.Value);
    if (!boundsMatch.Success) return null;
    var x1 = int.Parse(boundsMatch.Groups[1].Value);
    var y1 = int.Parse(boundsMatch.Groups[2].Value);
    var x2 = int.Parse(boundsMatch.Groups[3].Value);
    var y2 = int.Parse(boundsMatch.Groups[4].Value);
    return ((x1 + x2) / 2, (y1 + y2) / 2);
  }

  public bool TapMatch(InputBridge input, string pattern)
  {
    var pt = FindBounds(pattern);
    if (pt is null) return false;
    Log($"ui_tap: {pattern} @ {pt.Value.x},{pt.Value.y}");
    input.Tap(pt.Value.x, pt.Value.y);
    return true;
  }

  public bool TapAny(InputBridge input, params string[] patterns)
  {
    foreach (var p in patterns)
      if (TapMatch(input, p)) return true;
    return false;
  }

  public bool HasText(string pattern)
  {
    if (!Dump()) return false;
    var xml = File.ReadAllText(FarmPaths.UiDumpFile);
    return Regex.IsMatch(xml, pattern, RegexOptions.IgnoreCase);
  }

  public bool WaitText(string pattern, int maxSeconds = 30)
  {
    for (var i = 0; i < maxSeconds; i++)
    {
      if (HasText(pattern)) return true;
      Thread.Sleep(1000);
    }
    return false;
  }

  public void DismissPopups(InputBridge input) =>
    TapAny(input, "Close", "Not now", "Skip", "Cancel", "Later", "OK", "Got it", "Decline", "No thanks", "Confirm");
}
