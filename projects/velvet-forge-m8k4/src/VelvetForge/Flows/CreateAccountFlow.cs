namespace VelvetForge.Flows;

public static class CreateAccountFlow
{
  const string TikTokPackage = "com.zhiliaoapp.musically";

  public static int Run(FarmContext ctx)
  {
    ctx.Log?.Invoke("==> create-account");
    ctx.Ld.Launch();
    Thread.Sleep(3000);
    ctx.SyncScreen();

    ctx.Ld.KillApp(TikTokPackage);
    ctx.Ld.KillApp(ctx.Settings.MlbbPackage);
    Thread.Sleep(1500);
    ctx.Adb.Connect();
    ctx.Ld.RunApp(ctx.Settings.MlbbPackage);
    Thread.Sleep(12000);
    ctx.SyncScreen();
    ctx.Screenshot("create-start");

    for (var step = 0; step < 12; step++)
    {
      ctx.SyncScreen();
      ctx.Screenshot($"create-{step:D2}");

      if (IsMainLobby(ctx))
      {
        ctx.Log?.Invoke("create-account: main lobby reached");
        ctx.Screenshot("create-done");
        Console.WriteLine("create-account: ok");
        return 0;
      }

      HandleDialogs(ctx);
      ConfirmCreateCharacter(ctx);
      SelectRegion(ctx);
      TutorialTap(ctx);
    }

    ctx.Screenshot("create-final");
    if (IsMainLobby(ctx))
    {
      Console.WriteLine("create-account: ok");
      return 0;
    }

    Console.WriteLine("create-account: incomplete — check data/screenshots/create-*.png");
    return 1;
  }

  static bool IsMainLobby(FarmContext ctx)
  {
    if (!ctx.Ui.Available) return false;
    return ctx.Ui.HasText("Classic") || ctx.Ui.HasText("Battle") || ctx.Ui.HasText("BATTLE");
  }

  static void HandleDialogs(FarmContext ctx)
  {
    if (ctx.Ui.Available)
    {
      if (ctx.Ui.HasText("Quit"))
        ctx.Ui.TapMatch(ctx.Input, "Cancel");
      if (ctx.Ui.HasText("System Notice") || ctx.Ui.HasText("represent"))
        ctx.Ui.TapMatch(ctx.Input, "Okay");
    }
    ctx.Input.TapScale(0.42, 0.56);
    Thread.Sleep(400);
    ctx.Input.TapScale(0.62, 0.56);
    Thread.Sleep(600);
  }

  static void ConfirmCreateCharacter(FarmContext ctx)
  {
    ctx.Ld.SendKeyboard("W");
    Thread.Sleep(600);
    ctx.Input.TapScale(0.70, 0.34);
    Thread.Sleep(400);
    ctx.Input.TapScale(0.58, 0.54);
    Thread.Sleep(1000);
  }

  static void SelectRegion(FarmContext ctx)
  {
    if (ctx.Ui.Available && !ctx.Ui.HasText("country") && !ctx.Ui.HasText("region"))
      return;
    ctx.Log?.Invoke("create-account: pick Singapore");
    ctx.Input.TapScale(0.14, 0.42);
    Thread.Sleep(1000);
    ctx.Input.TapScale(0.62, 0.56);
    Thread.Sleep(1000);
  }

  static void TutorialTap(FarmContext ctx)
  {
    ctx.Input.TapScale(0.50, 0.85);
    Thread.Sleep(800);
  }
}
