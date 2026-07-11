namespace AmberHarbor.Core;

public sealed class AdbClient(LdPlayer ld, Action<string>? log = null)
{
  private readonly LdPlayer _ld = ld;
  public string Serial { get; private set; } = "";

  private void Log(string msg)
  {
    log?.Invoke(msg);
    Console.WriteLine(msg);
  }

  public void InitSerial()
  {
    var ports = AdbPortResolver.CandidatePorts(_ld.Home, _ld.Index).ToList();
    Serial = $"127.0.0.1:{ports[0]}";
    _candidatePorts = ports;
  }

  private List<int> _candidatePorts = [5555];

  public void ServerReset()
  {
    RunAdb("kill-server");
    Thread.Sleep(1000);
    RunAdb("start-server");
  }

  public void Connect(int? port = null)
  {
    InitSerial();
    ServerReset();
    var ports = port.HasValue ? new List<int> { port.Value } : _candidatePorts;
    foreach (var p in ports)
    {
      Log($"adb: connect 127.0.0.1:{p}");
      RunAdb("connect", $"127.0.0.1:{p}");
      Thread.Sleep(800);
      if (DeviceReady())
      {
        Serial = $"127.0.0.1:{p}";
        return;
      }
    }
  }

  public bool DeviceReady()
  {
    var output = RunAdb("devices");
    var port = FleetIndex.AdbPort(_ld.Index);
    var prefer = new[] { FleetIndex.AdbSerial(_ld.Index), FleetIndex.EmulatorSerial(_ld.Index), $"127.0.0.1:{port}" };
    foreach (var line in output.Split('\n', StringSplitOptions.RemoveEmptyEntries))
    {
      if (line.Contains("List of devices")) continue;
      if (!line.TrimEnd().EndsWith("device", StringComparison.Ordinal)) continue;
      foreach (var serial in prefer)
        if (line.Contains(serial, StringComparison.Ordinal))
          return true;
    }
    foreach (var line in output.Split('\n', StringSplitOptions.RemoveEmptyEntries))
    {
      if (line.Contains("List of devices")) continue;
      if (line.TrimEnd().EndsWith("device", StringComparison.Ordinal))
        return true;
    }
    return false;
  }

  public bool WaitReady(int maxSeconds = 90)
  {
    Log($"adb: waiting for device (max {maxSeconds}s)...");
    var elapsed = 0;
    while (elapsed < maxSeconds)
    {
      if (DeviceReady())
      {
        Log("adb: device ready");
        return true;
      }
      Connect();
      _ld.AdbCommand("shell input keyevent KEYCODE_WAKEUP");
      Thread.Sleep(2000);
      elapsed += 2;
    }
    return false;
  }

  public string RunAdb(params string[] args)
  {
    var psi = new System.Diagnostics.ProcessStartInfo(_ld.AdbBin, string.Join(" ", args))
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

  public string Cmd(params string[] args)
  {
    if (DeviceReady())
    {
      InitSerial();
      var withSerial = new[] { "-s", Serial }.Concat(args).ToArray();
      return RunAdb(withSerial);
    }
    return _ld.AdbCommand(string.Join(" ", args));
  }

  public string Shell(string command) => Cmd("shell", command);

  public void Tap(int x, int y) => Shell($"input tap {x} {y}");

  public void Swipe(int x1, int y1, int x2, int y2, int dur = 300) =>
    Shell($"input swipe {x1} {y1} {x2} {y2} {dur}");

  public void Text(string text)
  {
    var escaped = text.Replace(" ", "%s");
    Shell($"input text {escaped}");
  }

  public void PushFile(string localPath, string remotePath)
  {
    if (DeviceReady())
      Cmd("push", Quote(localPath), remotePath);
    else
      _ld.Push(localPath, remotePath);
  }

  private static string Quote(string path) => path.Contains(' ') ? $"\"{path}\"" : path;

  public bool EnsureReady(FarmSettings settings)
  {
    _ld.Launch();
    Thread.Sleep(3000);
    _ld.ModifyRoot();
    if (WaitReady(15)) return true;

    Log("adb: not ready — enabling adbDebug in leidian config");
    if (LdConfig.EnsureAdbEnabled(_ld.Home, _ld.Index, Log))
    {
      _ld.Reboot();
      Thread.Sleep(12000);
      Connect();
      if (WaitReady(120)) return true;
    }

    Log("adb: config patch failed — running enable_adb UI helper");
    var script = FarmPaths.EnableAdbScript;
    if (File.Exists(script))
    {
      var psi = new System.Diagnostics.ProcessStartInfo("powershell.exe",
        $"-NoProfile -ExecutionPolicy Bypass -File \"{script}\" -LdIndex {_ld.Index}")
      {
        UseShellExecute = false,
        RedirectStandardOutput = true,
        RedirectStandardError = true,
      };
      using var p = System.Diagnostics.Process.Start(psi)!;
      p.WaitForExit(120_000);
      Thread.Sleep(2000);
      _ld.Reboot();
      Thread.Sleep(8000);
      Connect();
      if (WaitReady(120)) return true;
    }

    Log("adb: enable failed — set basicSettings.adbDebug=1 in leidian config or LDPlayer Settings → Advanced → ADB");
    return false;
  }
}
