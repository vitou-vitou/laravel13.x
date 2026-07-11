namespace AmberHarbor.Flows;

public static class ProbeFlow
{
  public static int Run(FarmContext ctx)
  {
    if (!ctx.Adb.EnsureReady(ctx.Settings))
    {
      Console.WriteLine("probe: adb_unavailable");
      return 1;
    }

    ctx.Ld.RunApp();
    Thread.Sleep(8000);
    ctx.Ui.DismissPopups();
    ctx.Ui.TapAnyOr(1450, 850, "Profile", "Me", "profile");
    Thread.Sleep(3000);

    var hasProfile = ctx.Ui.HasText("Profile") || ctx.Ui.HasText("Sign up") || ctx.Ui.HasText("Log in");
    Console.WriteLine($"probe: profile_visible={hasProfile}");
    ctx.Ui.ScreenshotStep("probe", ctx.Ld);
    return hasProfile ? 0 : 1;
  }
}
