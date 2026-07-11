namespace VelvetForge.Core;

public sealed class AdbClient(LdPlayer ld, Action<string>? log = null)
{
  private readonly LdPlayer _ld = ld;
  public string Serial { get; private set; } = "";
  public bool IsReady { get; set; }

  private void Log(string msg)
  {
    log?.Invoke(msg);
    Console.WriteLine(msg);
  }

  public void InitSerial()
  {
    var port = _ld.ResolveAdbPort();
    Serial = $"127.0.0.1:{port}";
  }

  public void ServerReset()
  {
    RunAdb("kill-server");
    Thread.Sleep(800);
    RunAdb("start-server");
  }

  public void Connect()
  {
    InitSerial();
    ServerReset();
    var port = _ld.ResolveAdbPort();
    RunAdb("connect", $"127.0.0.1:{port}");
    Thread.Sleep(1000);
    IsReady = DeviceReady();
  }

  public bool DeviceReady()
  {
    var output = RunAdb("devices");
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
    Log($"adb: waiting for device (max {maxSeconds}s, port {_ld.ResolveAdbPort()})...");
    var elapsed = 0;
    while (elapsed < maxSeconds)
    {
      if (DeviceReady())
      {
        IsReady = true;
        Log("adb: device ready");
        return true;
      }
      Connect();
      _ld.AdbCommand("shell input keyevent KEYCODE_WAKEUP");
      Thread.Sleep(2000);
      elapsed += 2;
    }
    IsReady = false;
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

  public void Key(int keyCode) => Shell($"input keyevent {keyCode}");

  public void Back() => Key(4);

  public void Text(string text)
  {
    var escaped = text.Replace(" ", "%s");
    Shell($"input text {escaped}");
  }

  public bool EnsureReady(FarmSettings settings)
  {
    _ld.Launch();
    Thread.Sleep(3000);
    _ld.ModifyRoot();
    if (WaitReady()) return true;

    Log("adb: not ready — running enable_adb helper");
    if (File.Exists(FarmPaths.EnableAdbScript))
    {
      var psi = new System.Diagnostics.ProcessStartInfo("powershell.exe",
        $"-NoProfile -ExecutionPolicy Bypass -File \"{FarmPaths.EnableAdbScript}\" -LdIndex {settings.LdPlayerIndex}")
      {
        UseShellExecute = false,
        RedirectStandardOutput = true,
        RedirectStandardError = true,
      };
      using var p = System.Diagnostics.Process.Start(psi)!;
      p.WaitForExit(120_000);
      Thread.Sleep(2000);
      _ld.Reboot();
      Thread.Sleep(10000);
      Connect();
      return WaitReady(120);
    }

    Log("adb: enable Scripts/enable_adb.ps1 or LDPlayer Settings → Other → ADB Open Local Connection");
    return false;
  }
}
