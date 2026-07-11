using AmberHarbor.Flows;

namespace AmberHarbor;

public sealed class MainForm : Form
{
  private readonly TextBox _log = new() { Multiline = true, ScrollBars = ScrollBars.Vertical, Dock = DockStyle.Fill, ReadOnly = true, Font = new Font("Consolas", 9f) };
  private readonly CheckBox _ack = new() { Text = "Research ack (--ack-research-only)", AutoSize = true };
  private FarmContext? _ctx;

  public MainForm()
  {
    Text = "amber-harbor-ll8b — LDPlayer TikTok Farm";
    Width = 900;
    Height = 640;
    StartPosition = FormStartPosition.CenterScreen;

    var top = new FlowLayoutPanel { Dock = DockStyle.Top, AutoSize = true, Padding = new Padding(8) };
    foreach (var (label, cmd) in new (string, string)[]
    {
      ("Preflight", "preflight"),
      ("Probe", "probe"),
      ("Signup", "signup"),
      ("Login", "login"),
      ("Post", "post"),
      ("Cycle", "cycle"),
      ("Enable ADB", "enable-adb"),
    })
    {
      var b = new Button { Text = label, AutoSize = true, Margin = new Padding(4) };
      var c = cmd;
      b.Click += (_, _) => RunCommand(c);
      top.Controls.Add(b);
    }
    top.Controls.Add(_ack);

    Controls.Add(_log);
    Controls.Add(top);

    try
    {
      _ctx = FarmContext.Create(AppendLog);
      AppendLog($"LDPlayer: {_ctx.Ld.Home}");
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
      AppendLog("Not initialized — check settings.json");
      return;
    }
    if ((cmd is "signup" or "cycle") && !_ack.Checked &&
        !Core.PolicyGate.HasResearchAck(Array.Empty<string>()))
    {
      AppendLog("Check research ack for signup/cycle");
      return;
    }

    SetButtons(false);
    AppendLog($"--- {cmd} ---");
    try
    {
      var exit = await Task.Run(() => cmd switch
      {
        "preflight" => PreflightFlow.Run(_ctx),
        "probe" => ProbeFlow.Run(_ctx),
        "signup" => SignupFlow.Run(_ctx).exitCode,
        "login" => LoginFlow.Run(_ctx),
        "post" => PostFlow.Run(_ctx),
        "cycle" => CycleFlow.Run(_ctx),
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

  private void SetButtons(bool enabled)
  {
    foreach (Control c in Controls)
      if (c is FlowLayoutPanel flp)
        foreach (Control b in flp.Controls)
          if (b is Button btn) btn.Enabled = enabled;
  }
}
