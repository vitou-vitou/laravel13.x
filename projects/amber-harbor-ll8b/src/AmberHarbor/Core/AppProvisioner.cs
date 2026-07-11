namespace AmberHarbor.Core;

public sealed class AppProvisioner(
  FarmSettings settings,
  LdPlayer ld,
  AdbClient adb,
  UiAutomator ui,
  Action<string>? log = null)
{
  private readonly PlayStoreInstaller _store = new(
    ld, adb, ui, settings.Apps.PlayStorePackage, log);
  private readonly LdStoreInstaller _ldStore = new(ld, adb, ui, log);

  private void Log(string msg)
  {
    log?.Invoke(msg);
    Console.WriteLine(msg);
  }

  public bool EnsureSurfshark()
  {
    var pkg = settings.Fleet.Vpn.Package;
    if (ld.IsPackageInstalled(pkg))
    {
      Log($"apps: Surfshark already installed ({pkg})");
      return true;
    }

    Log("apps: Surfshark missing — installing");
    return InstallPackage(
      pkg,
      "Surfshark VPN",
      ResolveApk(settings.Apps.SurfsharkApk, "surfshark.apk", "Surfshark.apk"));
  }

  public bool EnsureTikTok()
  {
    var pkg = settings.TikTokPackage;
    if (ld.IsPackageInstalled(pkg))
    {
      Log($"apps: TikTok already installed ({pkg})");
      return true;
    }

    Log("apps: TikTok missing — installing");
    return InstallPackage(
      pkg,
      "TikTok",
      ResolveApk(settings.Apps.TikTokApk, "tiktok.apk", "TikTok.apk"));
  }

  private bool InstallPackage(string packageName, string storeSearch, string? apkPath)
  {
    if (!string.IsNullOrWhiteSpace(apkPath) && File.Exists(apkPath))
    {
      Log($"apps: installing APK {apkPath}");
      if (ld.InstallApk(apkPath, adb))
        return WaitPackage(packageName, 60);
      Log("apps: APK install failed — trying installapp");
    }

    Log($"apps: ldconsole installapp {packageName}");
    ld.InstallApp(packageName);
    if (WaitPackage(packageName, 30))
      return true;

    Log($"apps: installapp timeout — LDStore search '{storeSearch}'");
    _ldStore.Install(storeSearch, packageName, settings.Apps.InstallTimeoutSeconds);
    if (ld.IsPackageInstalled(packageName))
      return true;

    Log($"apps: LDStore timeout — Play Store search '{storeSearch}'");
    _store.Install(storeSearch, packageName, settings.Apps.InstallTimeoutSeconds);
    return ld.IsPackageInstalled(packageName);
  }

  private bool WaitPackage(string packageName, int seconds)
  {
    for (var i = 0; i < seconds; i += 5)
    {
      if (ld.IsPackageInstalled(packageName)) return true;
      Thread.Sleep(5000);
    }
    return ld.IsPackageInstalled(packageName);
  }

  private static string? ResolveApk(string configured, params string[] fileNames)
  {
    if (!string.IsNullOrWhiteSpace(configured) && File.Exists(configured))
      return configured;

    var roots = new[]
    {
      FarmPaths.ProjectRoot,
      AppContext.BaseDirectory,
      Path.Combine(FarmPaths.ProjectRoot, "assets", "apks"),
      Path.Combine(AppContext.BaseDirectory, "assets", "apks"),
    };

    foreach (var root in roots.Distinct())
    {
      foreach (var name in fileNames)
      {
        var path = Path.Combine(root, name);
        if (File.Exists(path)) return path;
        path = Path.Combine(root, "assets", "apks", name);
        if (File.Exists(path)) return path;
      }
    }

    return null;
  }
}
