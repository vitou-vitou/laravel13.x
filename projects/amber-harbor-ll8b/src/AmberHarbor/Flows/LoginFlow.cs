namespace AmberHarbor.Flows;

public static class LoginFlow
{
  public static int Run(FarmContext ctx, string? email = null, string? password = null)
  {
    if (!ctx.Adb.EnsureReady(ctx.Settings))
    {
      Console.WriteLine("login: adb_unavailable");
      return 1;
    }

    var acc = Core.AccountStore.Latest();
    email ??= acc?.Email;
    password ??= acc?.Password;
    if (string.IsNullOrWhiteSpace(email) || string.IsNullOrWhiteSpace(password))
    {
      Console.WriteLine("login: no_account");
      return 2;
    }

    Console.WriteLine($"==> login {email}");
    ctx.Ld.KillApp();
    Thread.Sleep(1000);
    ctx.Ld.RunApp();
    Thread.Sleep(8000);
    ctx.Ui.DismissPopups();

    ctx.Ui.TapAnyOr(1450, 850, "Profile", "Me");
    Thread.Sleep(3000);

    if (ctx.Ui.HasText("Log in") || ctx.Ui.HasText("Login"))
    {
      ctx.Ui.TapAny("Log in", "Login");
      Thread.Sleep(2000);
      ctx.Ui.TapAny("Use phone or email", "Phone or email");
      Thread.Sleep(2000);
      ctx.Ui.TapAny("Email", "Log in with email");
      Thread.Sleep(2000);

      ctx.Ui.TapAnyOr(800, 420, "Email", "Enter email");
      ctx.Adb.Text(email);
      Thread.Sleep(1000);
      ctx.Ui.TapAnyOr(800, 520, "Password");
      ctx.Adb.Text(password);
      Thread.Sleep(1000);
      ctx.Ui.TapAnyOr(800, 650, "Log in", "Login", "Continue");
      Thread.Sleep(6000);
    }

    ctx.Ui.DismissPopups();
    if ((ctx.Ui.HasText("For You") || ctx.Ui.HasText("Home") || ctx.Ui.HasText("Upload") || ctx.Ui.HasText("Profile"))
        && !ctx.Ui.HasText("Log in"))
    {
      Core.AccountStore.MarkLogin(email);
      ctx.Ui.ScreenshotStep("login-success", ctx.Ld);
      Console.WriteLine($"login: success email={email}");
      return 0;
    }

    ctx.Ui.ScreenshotStep("login-incomplete", ctx.Ld);
    Console.WriteLine($"login: incomplete email={email}");
    return 1;
  }
}
