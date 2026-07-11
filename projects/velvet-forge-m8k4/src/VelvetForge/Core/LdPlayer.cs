namespace VelvetForge.Core;

public sealed record LdInstanceInfo(
  int Index,
  string Title,
  int TopHwnd,
  int BindHwnd,
  int AndroidStarted,
  int Pid,
  int VboxPid,
  int Width,
  int Height,
  int Dpi);

public sealed class LdPlayer(FarmSettings settings, Action<string>? log = null)
{
  private readonly FarmSettings _s = settings;
  private string? _home;
  private int? _adbPort;

  public string Home => _home ??= ResolveHome();
  public string LdConsole => Path.Combine(Home, "ldconsole.exe");
  public string AdbBin => Path.Combine(Home, "adb.exe");
  public int Index => _s.LdPlayerIndex;

  private void Log(string msg)
  {
    log?.Invoke(msg);
    Console.WriteLine(msg);
  }

  public string ResolveHome()
  {
    var candidates = new List<string>();
    if (!string.IsNullOrWhiteSpace(_s.LdPlayerHome)) candidates.Add(_s.LdPlayerHome);
    var env = Environment.GetEnvironmentVariable("LDPLAYER_HOME");
    if (!string.IsNullOrWhiteSpace(env)) candidates.Add(env);
    candidates.AddRange(
    [
      @"D:\LDPlayer\LDPlayer9",
      @"C:\Program Files\LDPlayer\LDPlayer9",
      @"C:\Program Files (x86)\LDPlayer\LDPlayer9",
    ]);
    foreach (var c in candidates.Distinct())
    {
      if (File.Exists(Path.Combine(c, "ldconsole.exe"))) return c;
    }
    throw new FileNotFoundException("LDPlayer not found. Set ldplayerHome in settings.json");
  }

  public int ResolveAdbPort()
  {
    if (_adbPort is not null) return _adbPort.Value;
    var configPath = Path.Combine(Home, "config", "ld.config");
    if (File.Exists(configPath))
    {
      foreach (var line in File.ReadAllLines(configPath))
      {
        if (line.StartsWith("adb_port=", StringComparison.OrdinalIgnoreCase) &&
            int.TryParse(line["adb_port=".Length..], out var port))
        {
          _adbPort = port;
          return port;
        }
      }
    }
    _adbPort = 5555 + Index * 2;
    return _adbPort.Value;
  }

  public string Run(params string[] args)
  {
    var psi = new System.Diagnostics.ProcessStartInfo(LdConsole, string.Join(" ", args))
    {
      RedirectStandardOutput = true,
      RedirectStandardError = true,
      UseShellExecute = false,
      CreateNoWindow = true,
    };
    using var p = System.Diagnostics.Process.Start(psi)!;
    var stdout = p.StandardOutput.ReadToEnd();
    var stderr = p.StandardError.ReadToEnd();
    p.WaitForExit();
    return (stdout + stderr).Trim();
  }

  public LdInstanceInfo? GetInstanceInfo()
  {
    var lines = Run("list2").Split('\n', StringSplitOptions.RemoveEmptyEntries);
    foreach (var line in lines)
    {
      if (!line.StartsWith($"{Index},", StringComparison.Ordinal)) continue;
      var parts = line.Split(',');
      if (parts.Length < 10) return null;
      return new LdInstanceInfo(
        int.Parse(parts[0]),
        parts[1],
        int.Parse(parts[2]),
        int.Parse(parts[3]),
        int.Parse(parts[4]),
        int.Parse(parts[5]),
        int.Parse(parts[6]),
        int.Parse(parts[7]),
        int.Parse(parts[8]),
        int.Parse(parts[9]));
    }
    return null;
  }

  public bool IsRunning()
  {
    var outText = Run("isrunning", "--index", Index.ToString());
    return outText.Contains("running", StringComparison.OrdinalIgnoreCase);
  }

  public void Launch()
  {
    if (IsRunning())
    {
      Log($"ldplayer: already running index={Index}");
      return;
    }
    Log($"ldplayer: launching index={Index}");
    Run("launch", "--index", Index.ToString());
  }

  public void Reboot()
  {
    Log($"ldplayer: reboot index={Index}");
    Run("reboot", "--index", Index.ToString());
  }

  public void ModifyRoot() => Run("modify", "--index", Index.ToString(), "--root", "1");

  public void RunApp(string? pkg = null)
  {
    pkg ??= _s.MlbbPackage;
    Run("runapp", "--index", Index.ToString(), "--packagename", pkg);
  }

  public void KillApp(string? pkg = null)
  {
    pkg ??= _s.MlbbPackage;
    Run("killapp", "--index", Index.ToString(), "--packagename", pkg);
  }

  public bool Screenshot(string outPath)
  {
    FarmPaths.EnsureDataDirs();
    Directory.CreateDirectory(Path.GetDirectoryName(outPath)!);
    Run("scan", "--index", Index.ToString(), "--file", outPath);
    return File.Exists(outPath);
  }

  public string AdbCommand(string command) =>
    Run("adb", "--index", Index.ToString(), "--command", command);

  public void SendKeyboard(string key) =>
    Run("action", "--index", Index.ToString(), "--key", "call.keyboard", "--value", key);

  public bool IsPackageInstalled(string package)
  {
    var output = AdbCommand($"shell pm path {package}");
    return output.Contains("package:", StringComparison.OrdinalIgnoreCase);
  }
}
