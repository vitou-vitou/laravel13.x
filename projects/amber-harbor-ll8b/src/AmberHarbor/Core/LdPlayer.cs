namespace AmberHarbor.Core;

public sealed class LdPlayer
{
  private readonly FarmSettings _s;
  private readonly int _index;
  private readonly Action<string>? _log;
  private string? _home;

  public LdPlayer(FarmSettings settings, Action<string>? log = null, int? indexOverride = null)
  {
    _s = settings;
    _log = log;
    _index = indexOverride ?? settings.LdPlayerIndex;
  }

  public string Home => _home ??= ResolveHome();
  public string LdConsole => Path.Combine(Home, "ldconsole.exe");
  public string AdbBin => Path.Combine(Home, "adb.exe");
  public int Index => _index;

  private void Log(string msg)
  {
    _log?.Invoke(msg);
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
      @"D:\LDPlayer\LDPlayer4.0",
    ]);
    foreach (var c in candidates.Distinct())
    {
      var exe = Path.Combine(c, "ldconsole.exe");
      if (File.Exists(exe)) return c;
    }
    throw new FileNotFoundException("LDPlayer not found. Set ldplayerHome in settings.json");
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

  public IReadOnlyList<LdInstanceInfo> ListInstances()
  {
    var lines = Run("list2").Split('\n', StringSplitOptions.RemoveEmptyEntries);
    var list = new List<LdInstanceInfo>();
    foreach (var line in lines)
    {
      var parts = line.Split(',');
      if (parts.Length < 10) continue;
      if (!int.TryParse(parts[0], out var idx)) continue;
      list.Add(new LdInstanceInfo(
        idx,
        parts[1],
        int.Parse(parts[2]),
        int.Parse(parts[3]),
        int.Parse(parts[4]),
        int.Parse(parts[5]),
        int.Parse(parts[6]),
        int.Parse(parts[7]),
        int.Parse(parts[8]),
        int.Parse(parts[9])));
    }
    return list;
  }

  public LdInstanceInfo? GetInstanceInfo()
  {
    return ListInstances().FirstOrDefault(i => i.Index == Index);
  }

  public bool InstanceExists() => GetInstanceInfo() is not null;

  public void CopyFrom(int fromIndex, string name) =>
    Run("copy", "--from", fromIndex.ToString(), "--name", name);

  public void ApplyFleetProfile(FleetSettings fleet) =>
    Run(
      "modify",
      "--index", Index.ToString(),
      "--resolution", fleet.Resolution,
      "--memory", fleet.MemoryMb.ToString(),
      "--cpu", fleet.CpuCount.ToString(),
      "--root", "1");

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
    pkg ??= _s.TikTokPackage;
    Run("runapp", "--index", Index.ToString(), "--packagename", pkg);
  }

  public void KillApp(string? pkg = null)
  {
    pkg ??= _s.TikTokPackage;
    Run("killapp", "--index", Index.ToString(), "--packagename", pkg);
  }

  public void Push(string localPath, string remotePath)
  {
    Run("push", "--index", Index.ToString(), "--local", localPath, "--remote", remotePath);
  }

  public bool Screenshot(string outPath)
  {
    Directory.CreateDirectory(Path.GetDirectoryName(outPath)!);
    var serials = new[] { FleetIndex.EmulatorSerial(Index), FleetIndex.AdbSerial(Index) };
    foreach (var serial in serials)
    {
      try
      {
        var psi = new System.Diagnostics.ProcessStartInfo(AdbBin, $"-s {serial} exec-out screencap -p")
        {
          RedirectStandardOutput = true,
          RedirectStandardError = true,
          UseShellExecute = false,
          CreateNoWindow = true,
        };
        using var p = System.Diagnostics.Process.Start(psi)!;
        using (var fs = File.Create(outPath))
          p.StandardOutput.BaseStream.CopyTo(fs);
        p.WaitForExit();
        if (p.ExitCode == 0 && File.Exists(outPath) && new FileInfo(outPath).Length > 0)
          return true;
      }
      catch { /* try next serial */ }
    }

    Log("ldplayer: adb screencap failed");
    return false;
  }

  public string AdbCommand(string command) =>
    Run("adb", "--index", Index.ToString(), "--command", command);

  public string InstallApp(string packageName) =>
    Run("installapp", "--index", Index.ToString(), "--packagename", packageName);

  public bool InstallApk(string localPath, AdbClient? adb = null)
  {
    if (!File.Exists(localPath))
    {
      Log($"ldplayer: APK not found {localPath}");
      return false;
    }

    Log($"ldplayer: installapp --filename {localPath}");
    var ldOut = Run("installapp", "--index", Index.ToString(), "--filename", localPath);
    if (ldOut.Contains("success", StringComparison.OrdinalIgnoreCase)
        || ldOut.Contains("installed", StringComparison.OrdinalIgnoreCase))
      return true;

    var remote = $"/sdcard/{Path.GetFileName(localPath)}";
    Push(localPath, remote);
    var output = adb is not null && adb.DeviceReady()
      ? adb.Shell($"pm install -r {remote}")
      : AdbCommand($"shell pm install -r {remote}");
    var ok = output.Contains("Success", StringComparison.OrdinalIgnoreCase);
    Log(ok ? $"ldplayer: APK installed {Path.GetFileName(localPath)}" : $"ldplayer: APK install failed — {output}");
    return ok;
  }

  public bool IsPackageInstalled(string package)
  {
    var output = AdbCommand($"shell pm path {package}");
    return output.Contains("package:", StringComparison.OrdinalIgnoreCase);
  }
}
