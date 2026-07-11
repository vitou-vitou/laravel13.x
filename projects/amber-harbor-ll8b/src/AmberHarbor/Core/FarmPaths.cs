namespace AmberHarbor.Core;

public static class FarmPaths
{
  public static string ProjectRoot => _projectRoot ??= ResolveProjectRoot();

  private static string? _projectRoot;

  private static string ResolveProjectRoot()
  {
    var candidates = new List<string>
    {
      Environment.CurrentDirectory,
      AppContext.BaseDirectory.TrimEnd(Path.DirectorySeparatorChar),
    };

    var dir = AppContext.BaseDirectory;
    for (var i = 0; i < 8; i++)
    {
      candidates.Add(dir.TrimEnd(Path.DirectorySeparatorChar));
      var parent = Directory.GetParent(dir)?.FullName;
      if (parent is null) break;
      dir = parent;
    }

    foreach (var c in candidates.Distinct())
    {
      if (File.Exists(Path.Combine(c, "settings.json")))
        return c;
    }

    foreach (var c in candidates.Distinct())
    {
      if (File.Exists(Path.Combine(c, "settings.example.json")))
        return c;
    }

    return AppContext.BaseDirectory;
  }

  public static string SettingsFile => Path.Combine(ProjectRoot, "settings.json");
  public static string AccountsFile => Path.Combine(ProjectRoot, "data", "accounts.json");
  public static string ScreenshotDir => Path.Combine(ProjectRoot, "data", "screenshots");
  public static string UiDumpFile => Path.Combine(ProjectRoot, "data", "window_dump.xml");
  public static string SampleVideo
  {
    get
    {
      var candidates = new[]
      {
        Path.Combine(ProjectRoot, "assets", "sample.mp4"),
        Path.Combine(AppContext.BaseDirectory, "assets", "sample.mp4"),
      };
      return candidates.FirstOrDefault(File.Exists) ?? candidates[0];
    }
  }

  public static string EnableAdbScript
  {
    get
    {
      var candidates = new[]
      {
        Path.Combine(AppContext.BaseDirectory, "Scripts", "enable_adb.ps1"),
        Path.Combine(ProjectRoot, "src", "AmberHarbor", "Scripts", "enable_adb.ps1"),
        Path.Combine(ProjectRoot, "Scripts", "enable_adb.ps1"),
      };
      return candidates.FirstOrDefault(File.Exists) ?? candidates[0];
    }
  }

  public static void EnsureDataDirs()
  {
    Directory.CreateDirectory(Path.GetDirectoryName(AccountsFile)!);
    Directory.CreateDirectory(ScreenshotDir);
  }
}
