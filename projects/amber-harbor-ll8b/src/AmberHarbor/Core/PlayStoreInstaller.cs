namespace AmberHarbor.Core;

public sealed class PlayStoreInstaller(
  LdPlayer ld,
  AdbClient adb,
  UiAutomator ui,
  string playStorePackage,
  Action<string>? log = null)
{
  private void Log(string msg)
  {
    log?.Invoke(msg);
    Console.WriteLine(msg);
  }

  public bool Install(string searchTerm, string packageName, int timeoutSeconds = 180)
  {
    Log($"playstore: install {searchTerm} ({packageName})");
    OpenPlayStore();
    Thread.Sleep(5000);
    ui.DismissPopups();

    if (!ui.TapMatch("Search") && !ui.TapMatch("Search apps"))
      adb.Tap(650, 120);
    Thread.Sleep(2000);

    adb.Shell("input keyevent KEYCODE_MOVE_END");
    for (var i = 0; i < 40; i++)
      adb.Shell("input keyevent KEYCODE_DEL");
    Thread.Sleep(200);
    adb.Text(searchTerm);
    Thread.Sleep(1500);
    adb.Shell("input keyevent KEYCODE_ENTER");
    Thread.Sleep(4000);

    if (!ui.TapMatch(searchTerm) && !ui.TapMatch("Surfshark"))
      ui.TapMatch("TikTok");
    Thread.Sleep(4000);
    ui.DismissPopups();

    if (!ui.TapMatch("Install") && !ui.TapMatch("Get"))
      ui.TapMatch("Update");
    if (!ui.HasText("Installing") && !ui.HasText("Open"))
      adb.Tap(360, 900);
    Thread.Sleep(3000);

    if (ui.HasText("Accept"))
      ui.TapMatch("Accept");
    else if (ui.HasText("Agree"))
      ui.TapMatch("Agree");

    return WaitInstalled(packageName, timeoutSeconds);
  }

  private void OpenPlayStore()
  {
    adb.Shell($"monkey -p {playStorePackage} -c android.intent.category.LAUNCHER 1");
    Thread.Sleep(2000);
    if (!ui.HasText("Play") && !ui.HasText("Search"))
    {
      ld.Run("runapp", "--index", ld.Index.ToString(), "--packagename", playStorePackage);
      Thread.Sleep(3000);
    }
  }

  private bool WaitInstalled(string packageName, int timeoutSeconds)
  {
    Log($"playstore: waiting for {packageName} (max {timeoutSeconds}s)");
    var deadline = DateTime.UtcNow.AddSeconds(timeoutSeconds);
    while (DateTime.UtcNow < deadline)
    {
      if (ld.IsPackageInstalled(packageName))
      {
        Log($"playstore: installed {packageName}");
        return true;
      }
      if (ui.HasText("Open") || ui.HasText("Play"))
      {
        Log("playstore: install complete (Open/Play visible)");
        return ld.IsPackageInstalled(packageName);
      }
      Thread.Sleep(5000);
    }
    return ld.IsPackageInstalled(packageName);
  }
}
