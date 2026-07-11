namespace AmberHarbor.Core;

public sealed class LdStoreInstaller(
  LdPlayer ld,
  AdbClient adb,
  UiAutomator ui,
  Action<string>? log = null)
{
  private const string LdStorePackage = "com.android.ld.appstore";

  private void Log(string msg)
  {
    log?.Invoke(msg);
    Console.WriteLine(msg);
  }

  public bool Install(string searchTerm, string packageName, int timeoutSeconds = 180)
  {
    Log($"ldstore: install {searchTerm} ({packageName})");
    adb.Shell($"monkey -p {LdStorePackage} -c android.intent.category.LAUNCHER 1");
    Thread.Sleep(5000);
    ui.DismissPopups();

    if (!ui.TapMatch("Search") && !ui.TapMatch("search"))
      adb.Tap(700, 100);
    Thread.Sleep(1500);

    adb.Text(searchTerm.Replace(" ", "%s"));
    Thread.Sleep(2000);
    adb.Shell("input keyevent KEYCODE_ENTER");
    Thread.Sleep(4000);

    if (!ui.TapMatch(searchTerm) && !ui.TapMatch("Surfshark"))
      ui.TapMatch("TikTok");
    Thread.Sleep(3000);

    if (!ui.TapMatch("Install") && !ui.TapMatch("Download"))
      adb.Tap(400, 750);
    Thread.Sleep(3000);

    return WaitInstalled(packageName, timeoutSeconds);
  }

  private bool WaitInstalled(string packageName, int timeoutSeconds)
  {
    var deadline = DateTime.UtcNow.AddSeconds(timeoutSeconds);
    while (DateTime.UtcNow < deadline)
    {
      if (ld.IsPackageInstalled(packageName))
      {
        Log($"ldstore: installed {packageName}");
        return true;
      }
      Thread.Sleep(5000);
    }
    return ld.IsPackageInstalled(packageName);
  }
}
