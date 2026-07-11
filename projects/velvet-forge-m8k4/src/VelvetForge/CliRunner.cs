using VelvetForge.Flows;

namespace VelvetForge;

public static class CliRunner
{
  public static int Run(string[] args)
  {
    if (args.Length == 0) return -1;

    var cmd = args[0].ToLowerInvariant();
    var rest = args.Skip(1).ToArray();
    var hero = GetOpt(rest, "--hero");
    var duration = int.TryParse(GetOpt(rest, "--duration"), out var d) ? d : (int?)null;

    Action<string> log = s => Console.WriteLine(s);
    var ctx = FarmContext.Create(log);

    if (duration is not null)
      ctx.Settings.PlayDurationSeconds = duration.Value;

    try
    {
      return cmd switch
      {
        "preflight" => PreflightFlow.Run(ctx),
        "probe" => ProbeFlow.Run(ctx),
        "play-hero" => PlayHeroFlow.Run(ctx, hero),
        "cycle" => CycleFlow.Run(ctx, hero),
        "create-account" => CreateAccountFlow.Run(ctx),
        "enable-adb" => RunEnableAdb(ctx),
        "help" or "-h" or "--help" => PrintHelp(),
        _ => Unknown(cmd),
      };
    }
    catch (Exception ex)
    {
      Console.Error.WriteLine($"error: {ex.Message}");
      return 2;
    }
  }

  private static int RunEnableAdb(FarmContext ctx)
  {
    var script = Core.FarmPaths.EnableAdbScript;
    if (!File.Exists(script))
    {
      Console.WriteLine($"missing {script}");
      return 1;
    }
    var psi = new System.Diagnostics.ProcessStartInfo("powershell.exe",
      $"-NoProfile -ExecutionPolicy Bypass -File \"{script}\" -LdIndex {ctx.Settings.LdPlayerIndex}")
    {
      UseShellExecute = false,
    };
    using var p = System.Diagnostics.Process.Start(psi)!;
    p.WaitForExit();
    return p.ExitCode;
  }

  private static string? GetOpt(string[] args, string name)
  {
    for (var i = 0; i < args.Length - 1; i++)
      if (args[i].Equals(name, StringComparison.OrdinalIgnoreCase))
        return args[i + 1];
    return null;
  }

  private static int Unknown(string cmd)
  {
    Console.Error.WriteLine($"Unknown command: {cmd}");
    return PrintHelp();
  }

  public static int PrintHelp()
  {
    Console.WriteLine("""
velvet-forge-m8k4 — LDPlayer MLBB hero active-play farm (C# WinExe 100%)

Usage:
  velvet-forge-m8k4 [command] [options]
  velvet-forge-m8k4                 Launch GUI

Commands:
  preflight          Launch LDPlayer, verify MLBB + input backend
  probe              Dump emulator state + screenshot
  play-hero          Classic match → pick hero → active play loop
  cycle              Same as play-hero (definition of done)
  create-account     Create one MLBB character (Create → Okay → tutorial)
  enable-adb         One-time LDPlayer ADB settings helper

Options:
  --hero NAME        Hero to play (default from settings.json)
  --duration SEC     Active play seconds (default 480)

Env: LDPLAYER_HOME
Input: ADB when available; falls back to Win32 HWND taps on bind window.
""");
    return 0;
  }
}
