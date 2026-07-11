namespace AmberHarbor.Core;

public sealed class VpnOrchestrator(
  LdPlayer ld,
  AdbClient adb,
  UiAutomator ui,
  VpnSettings vpn,
  Action<string>? log = null)
{
  private void Log(string msg)
  {
    log?.Invoke(msg);
    Console.WriteLine(msg);
  }

  public bool EnsureConnected(string targetCountry = "US", int timeoutSeconds = 120)
  {
    Log($"vpn: ensure Surfshark connected ({targetCountry}) index={ld.Index}");
    if (!IsPackageInstalled())
    {
      Log($"vpn: package not installed: {vpn.Package}");
      return false;
    }

    LaunchApp();
    Thread.Sleep(4000);
    ui.DismissPopups();
    DismissVpnPermissionDialog();

    if (NeedsManualLogin())
    {
      Log("vpn: Surfshark login required — sign in inside LDPlayer, then re-run setup");
      return false;
    }

    if (IsConnectedUi() && HasUsaSelected())
    {
      Log("vpn: already connected to USA (UI)");
      return true;
    }

    SetUsaBaseAndConnect(targetCountry);
    var deadline = DateTime.UtcNow.AddSeconds(timeoutSeconds);
    while (DateTime.UtcNow < deadline)
    {
      ui.DismissPopups();
      DismissVpnPermissionDialog();
      if (IsConnectedUi())
      {
        Log("vpn: connected (USA)");
        return true;
      }
      Thread.Sleep(3000);
    }

    Log("vpn: connect timeout");
    return false;
  }

  public bool NeedsManualLogin() =>
    ui.HasText("Log in") || ui.HasText("Sign in") || ui.HasText("Create account");

  public bool IsPackageInstalled()
  {
    var output = adb.Shell($"pm path {vpn.Package}");
    return output.Contains("package:", StringComparison.OrdinalIgnoreCase);
  }

  private void LaunchApp()
  {
    adb.Shell($"monkey -p {vpn.Package} -c android.intent.category.LAUNCHER 1");
    Thread.Sleep(1500);
  }

  private bool IsConnectedUi() =>
    ui.HasText("Connected") || ui.HasText("Disconnect") || ui.HasText("Pause");

  private bool HasUsaSelected() =>
    ui.HasText("United States") || ui.HasText("USA");

  public void SetUsaBaseAndConnect(string country = "US")
  {
    if (!ui.TapMatch("Locations") && !ui.TapMatch("Countries"))
      ui.TapMatch("VPN locations");
    Thread.Sleep(2500);
    ui.DismissPopups();

    if (!ui.TapMatch("United States") && !ui.TapMatch("USA"))
      ui.TapMatch(country);
    Thread.Sleep(3000);

    if (!ui.TapMatch("Fastest") && !ui.TapMatch("Connect"))
      ui.TapMatch("Quick connect");
    Thread.Sleep(2000);

    if (!IsConnectedUi() && !ui.TapMatch("Quick connect") && !ui.TapMatch("Connect"))
      adb.Tap(360, 1100);
    Thread.Sleep(3000);
  }

  private void DismissVpnPermissionDialog()
  {
    if (ui.TapMatch("OK") || ui.TapMatch("Allow"))
      Thread.Sleep(1500);
    if (ui.HasText("Connection request"))
      ui.TapMatch("OK");
    if (ui.HasText("always allow") || ui.HasText("Always allow"))
    {
      if (!ui.TapMatch("Always allow"))
        ui.TapMatch("OK");
    }
  }
}
