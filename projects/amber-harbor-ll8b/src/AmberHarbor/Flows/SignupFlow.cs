namespace AmberHarbor.Flows;

public static class SignupFlow
{
  public static (int exitCode, string email, string password) Run(
    FarmContext ctx, string? email = null, string birthMonth = "January")
  {
    if (!ctx.Adb.EnsureReady(ctx.Settings))
    {
      Console.WriteLine("signup: adb_unavailable");
      return (1, "", "");
    }

    email ??= ctx.Settings.AliasEmail("farm");
    var password = ctx.Settings.Password;
    Console.WriteLine($"==> signup {email} (LDPlayer TikTok app)");

    ctx.Ld.KillApp();
    Thread.Sleep(1000);
    ctx.Ld.RunApp();
    Thread.Sleep(8000);
    ctx.Ui.DismissPopups();

    ctx.Ui.TapAnyOr(1450, 850, "Profile", "Me", "profile");
    Thread.Sleep(3000);
    ctx.Ui.DismissPopups();

    ctx.Ui.TapAny("Sign up", "Sign Up", "Create account");
    Thread.Sleep(2000);
    ctx.Ui.TapAny("Use phone or email", "Phone or email", "Continue with email");
    Thread.Sleep(2000);
    ctx.Ui.TapAny("Email", "Sign up with email");
    Thread.Sleep(2000);

    ctx.Ui.TapAny(birthMonth, "Month");
    ctx.Ui.TapAny("15", "Day");
    ctx.Ui.TapAny("1995", "Year");
    Thread.Sleep(1000);

    if (!ctx.Ui.TapAny("Email", "Enter email")) ctx.Adb.Tap(800, 420);
    Thread.Sleep(500);
    ctx.Adb.Shell("input keyevent 123");
    ctx.Adb.Text(email);
    Thread.Sleep(1000);

    ctx.Ui.TapAnyOr(800, 520, "Password", "Enter password");
    Thread.Sleep(500);
    ctx.Adb.Text(password);
    Thread.Sleep(1000);

    var sendEpoch = DateTimeOffset.UtcNow.ToUnixTimeSeconds();
    ctx.Ui.TapAnyOr(800, 620, "Send code", "Get code", "Continue", "Next");
    Thread.Sleep(8000);
    ctx.Ui.ScreenshotStep("signup-sent", ctx.Ld);

    var code = ctx.Otp.Poll(email, sendEpoch);
    if (code is null)
    {
      ctx.Ui.ScreenshotStep("signup-otp-timeout", ctx.Ld);
      Console.WriteLine($"signup: otp_timeout email={email}");
      return (1, email, password);
    }

    ctx.Ui.TapAnyOr(800, 420, "6-digit", "verification code", "Code");
    Thread.Sleep(500);
    ctx.Adb.Text(code);
    Thread.Sleep(1000);
    ctx.Ui.TapAnyOr(800, 650, "Next", "Continue", "Verify");
    Thread.Sleep(6000);

    if (ctx.Ui.HasText("Username") || ctx.Ui.HasText("Create username"))
    {
      var uname = $"user_{Random.Shared.Next(0x10000, 0xfffffff):x}";
      ctx.Ui.TapAny("Username");
      ctx.Adb.Text(uname);
      ctx.Ui.TapAny("Next", "Continue");
      Thread.Sleep(4000);
    }

    ctx.Ui.DismissPopups();
    if (ctx.Ui.HasText("For You") || ctx.Ui.HasText("Home") || ctx.Ui.HasText("Following"))
    {
      Core.AccountStore.Upsert(email, password);
      ctx.Ui.ScreenshotStep("signup-success", ctx.Ld);
      Console.WriteLine($"signup: success email={email}");
      return (0, email, password);
    }

    ctx.Ui.ScreenshotStep("signup-incomplete", ctx.Ld);
    Console.WriteLine($"signup: incomplete email={email}");
    return (1, email, password);
  }
}
