using VelvetForge.Flows;
using VelvetForge.Mlbb;

namespace VelvetForge;

public sealed class MainForm : Form
{
  private readonly TextBox _log = new()
  {
    Multiline = true,
    ScrollBars = ScrollBars.Vertical,
    Dock = DockStyle.Fill,
    ReadOnly = true,
    Font = new Font("Consolas", 9f),
  };
  private readonly TextBox _hero = new() { Text = "Layla", Width = 120 };
  private readonly NumericUpDown _duration = new() { Minimum = 30, Maximum = 3600, Value = 120, Width = 80 };
  private FarmContext? _ctx;

  public MainForm()
  {
    Text = "velvet-forge-m8k4 — MLBB Hero Farm (LDPlayer)";
    Width = 960;
    Height = 680;
    StartPosition = FormStartPosition.CenterScreen;

    var top = new FlowLayoutPanel { Dock = DockStyle.Top, AutoSize = true, Padding = new Padding(8) };
    foreach (var (label, cmd) in new (string, string)[]
    {
      ("Preflight", "preflight"),
      ("Probe", "probe"),
      ("Play Hero", "play-hero"),
      ("Cycle", "cycle"),
      ("Create Account", "create-account"),
      ("Enable ADB", "enable-adb"),
    })
    {
      var b = new Button { Text = label, AutoSize = true, Margin = new Padding(4) };
      var c = cmd;
      b.Click += (_, _) => RunCommand(c);
      top.Controls.Add(b);
    }
    top.Controls.Add(new Label { Text = "Hero:", AutoSize = true, Padding = new Padding(8, 8, 0, 0) });
    top.Controls.Add(_hero);
    top.Controls.Add(new Label { Text = "Play sec:", AutoSize = true, Padding = new Padding(8, 8, 0, 0) });
    top.Controls.Add(_duration);

    Controls.Add(_log);
    Controls.Add(top);

    try
    {
      _ctx = FarmContext.Create(AppendLog);
      AppendLog($"LDPlayer: {_ctx.Ld.Home}");
      AppendLog($"Input: {_ctx.Input.ActiveBackend}");
      AppendLog($"Settings: {Core.FarmPaths.SettingsFile}");
    }
    catch (Exception ex)
    {
      AppendLog($"Init error: {ex.Message}");
    }
  }

  private void AppendLog(string line)
  {
    if (InvokeRequired)
    {
      BeginInvoke(() => AppendLog(line));
      return;
    }
    _log.AppendText(line + Environment.NewLine);
  }

  private async void RunCommand(string cmd)
  {
    if (_ctx is null)
    {
      AppendLog("Not initialized — copy settings.example.json → settings.json");
      return;
    }

    SetButtons(false);
    AppendLog($"--- {cmd} ---");
    try
    {
      var hero = _hero.Text.Trim();
      var dur = (int)_duration.Value;
      var exit = await Task.Run(() => cmd switch
      {
        "preflight" => PreflightFlow.Run(_ctx),
        "probe" => ProbeFlow.Run(_ctx),
        "play-hero" => RunPlayHero(_ctx, hero, dur),
        "cycle" => RunPlayHero(_ctx, hero, dur),
        "create-account" => CreateAccountFlow.Run(_ctx),
        "enable-adb" => CliRunner.Run(["enable-adb"]),
        _ => 1,
      });
      AppendLog($"{cmd}: exit {exit}");
    }
    catch (Exception ex)
    {
      AppendLog($"error: {ex.Message}");
    }
    finally
    {
      SetButtons(true);
    }
  }

  private static int RunPlayHero(FarmContext ctx, string hero, int duration)
  {
    ctx.Settings.HeroName = hero;
    ctx.Settings.PlayDurationSeconds = duration;
    return PlayHeroFlow.Run(ctx, hero);
  }

  private void SetButtons(bool enabled)
  {
    foreach (Control c in Controls)
    {
      if (c is not FlowLayoutPanel flp) continue;
      foreach (Control b in flp.Controls)
        if (b is Button btn) btn.Enabled = enabled;
    }
  }
}
