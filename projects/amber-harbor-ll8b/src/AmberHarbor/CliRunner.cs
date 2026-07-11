using AmberHarbor.Flows;

namespace AmberHarbor;

public static class CliRunner
{
  public static int Run(string[] args)
  {
    if (args.Length == 0) return -1;

    var cmd = args[0].ToLowerInvariant();
    var rest = Core.CliArgs.WithoutCommand(args);
    var email = Core.CliArgs.GetOpt(rest, "--email");
    var password = Core.CliArgs.GetOpt(rest, "--password");
    var video = Core.CliArgs.GetOpt(rest, "--video");
    var caption = Core.CliArgs.GetOpt(rest, "--caption") ?? "amber-harbor farm test #tiktok";
    var skipVpn = Core.CliArgs.HasFlag(rest, "--skip-vpn");
    var allArgs = args;

    Action<string> log = s => Console.WriteLine(s);
    var index = Core.CliArgs.GetIndex(rest);
    var ctx = FarmContext.Create(log, index);

    try
    {
      return cmd switch
      {
        "preflight" => PreflightFlow.Run(ctx, skipVpn),
        "probe" => ProbeFlow.Run(ctx),
        "signup" => RunSignup(ctx, allArgs, email),
        "login" => LoginFlow.Run(ctx, email, password),
        "post" => PostFlow.Run(ctx, email, video, caption),
        "cycle" => RunCycle(ctx, allArgs, caption, video, skipVpn),
        "enable-adb" => RunEnableAdb(ctx),
        "doctor" => DoctorFlow.Run(ctx),
        "setup" => SetupFlow.Run(ctx, Core.CliArgs.HasFlag(rest, "--skip-vpn")),
        "accounts" => RunAccounts(),
        "fleet" => RunFleet(ctx, rest),
        "vpn" => RunVpn(ctx, rest),
        "location" => RunLocation(ctx, rest),
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

  private static int RunFleet(FarmContext ctx, string[] rest)
  {
    var sub = rest.FirstOrDefault(a => !a.StartsWith('-'))?.ToLowerInvariant();
    var subRest = sub is null ? rest : rest.SkipWhile(a => !a.Equals(sub, StringComparison.OrdinalIgnoreCase)).Skip(1).ToArray();
    return sub switch
    {
      "init" => FleetInitFlow.Run(ctx, Core.CliArgs.GetIntOpt(subRest, "--count", ctx.Settings.Fleet.InstanceCount)),
      "status" => FleetStatusFlow.Run(ctx),
      "launch" => FleetLaunchFlow.Run(
        ctx,
        Core.CliArgs.GetOpt(subRest, "--count") is not null
          ? Core.CliArgs.GetIntOpt(subRest, "--count", ctx.Settings.Fleet.InstanceCount)
          : null,
        Core.CliArgs.GetIndex(subRest)),
      null or "" => PrintFleetHelp(),
      _ => PrintFleetHelp(),
    };
  }

  private static int RunVpn(FarmContext ctx, string[] rest)
  {
    var sub = rest.FirstOrDefault(a => !a.StartsWith('-'))?.ToLowerInvariant();
    return sub switch
    {
      "ensure" => VpnEnsureFlow.Run(ctx),
      null or "" => PrintVpnHelp(),
      _ => PrintVpnHelp(),
    };
  }

  private static int RunLocation(FarmContext ctx, string[] rest)
  {
    var sub = rest.FirstOrDefault(a => !a.StartsWith('-'))?.ToLowerInvariant();
    return sub switch
    {
      "verify" => LocationVerifyFlow.Run(ctx),
      null or "" => PrintLocationHelp(),
      _ => PrintLocationHelp(),
    };
  }

  private static int RunSignup(FarmContext ctx, string[] args, string? email)
  {
    Core.PolicyGate.RequireResearchAck(args);
    var (code, _, _) = SignupFlow.Run(ctx, email);
    return code;
  }

  private static int RunCycle(FarmContext ctx, string[] args, string caption, string? video, bool skipVpn)
  {
    Core.PolicyGate.RequireResearchAck(args);
    return CycleFlow.Run(ctx, caption, video, skipVpn);
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
      $"-NoProfile -ExecutionPolicy Bypass -File \"{script}\" -LdIndex {ctx.Index}")
    { UseShellExecute = false };
    using var p = System.Diagnostics.Process.Start(psi)!;
    p.WaitForExit(120_000);
    Thread.Sleep(10000);
    return p.ExitCode;
  }

  private static int RunAccounts()
  {
    var list = Core.AccountStore.Load();
    Console.WriteLine(System.Text.Json.JsonSerializer.Serialize(list, new System.Text.Json.JsonSerializerOptions { WriteIndented = true }));
    return 0;
  }

  private static int Unknown(string cmd)
  {
    Console.Error.WriteLine($"Unknown command: {cmd}");
    return PrintHelp();
  }

  public static int PrintHelp()
  {
    Console.WriteLine("""
amber-harbor-ll8b — LDPlayer TikTok farm (C# WinExe)

Usage:
  amber-harbor-ll8b [command] [options]
  amber-harbor-ll8b                 Launch GUI

Commands:
  preflight          Launch LDPlayer, ADB, VPN/location, verify TikTok
  probe              UIAutomator probe (Profile / Sign up)
  signup             Email signup + Gmail OTP (--ack-research-only)
  login              Login saved account
  post               Upload assets/sample.mp4
  cycle              preflight → signup → login → post (--ack-research-only)
  enable-adb         Patch leidian config + reboot for ADB
  doctor             Health check (ADB + TikTok)
  setup              Install Surfshark + TikTok, connect USA VPN
  accounts           List data/accounts.json
  fleet init         Clone template instances (--count N)
  fleet status       Show data/fleet.json + running state
  fleet launch       Launch instances (--count N --index start)
  vpn ensure         Connect Surfshark USA on --index instance
  location verify    ipinfo.io country check inside emulator

Options:
  --index N             LDPlayer instance (default: settings ldplayerIndex)
  --skip-vpn            Skip Surfshark + location in preflight/cycle
  --ack-research-only   Required for signup/cycle
  --email ADDR
  --password PASS
  --video PATH
  --caption TEXT

Env: TIKTOK_RESEARCH_ACK=1, LDPLAYER_HOME
""");
    return 0;
  }

  private static int PrintFleetHelp()
  {
    Console.WriteLine("fleet: init --count N | status | launch [--count N] [--index N]");
    return 0;
  }

  private static int PrintVpnHelp()
  {
    Console.WriteLine("vpn: ensure [--index N]");
    return 0;
  }

  private static int PrintLocationHelp()
  {
    Console.WriteLine("location: verify [--index N]");
    return 0;
  }
}
