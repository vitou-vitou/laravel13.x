namespace AmberHarbor.Flows;

public static class DoctorFlow
{
  public static int Run(FarmContext ctx)
  {
    Console.WriteLine("==> doctor");
    try
    {
      Console.WriteLine($"LDPlayer: {ctx.Ld.Home}");
      Console.WriteLine($"Running: {ctx.Ld.IsRunning()}");
      Console.WriteLine($"Sample video: {Core.FarmPaths.SampleVideo} exists={File.Exists(Core.FarmPaths.SampleVideo)}");
      Console.WriteLine($"Settings: {Core.FarmPaths.SettingsFile}");
      var port = Core.AdbPortResolver.ReadConfigPort(ctx.Ld.Home);
      Console.WriteLine($"ld.config adb_port: {port}");

      ctx.Adb.Connect();
      var ready = ctx.Adb.DeviceReady();
      Console.WriteLine($"ADB device ready: {ready}");
      if (!ready)
      {
        Console.WriteLine("FIX: run `enable-adb` or LDPlayer → Settings (gear) → Other → ADB Open local connection → Save → Restart");
        return 1;
      }

      ctx.Ld.RunApp();
      Thread.Sleep(6000);
      var tiktok = ctx.Ui.HasText("TikTok") || ctx.Ui.HasText("For You") || ctx.Ui.HasText("Profile")
        || ctx.Ui.HasText("Sign up");
      Console.WriteLine($"TikTok app visible: {tiktok}");
      if (!tiktok)
      {
        Console.WriteLine("FIX: install TikTok from Play Store inside LDPlayer");
        return 1;
      }

      Console.WriteLine("doctor: ok — ready for signup/login/post");
      return 0;
    }
    catch (Exception ex)
    {
      Console.WriteLine($"doctor: error {ex.Message}");
      return 2;
    }
  }
}
