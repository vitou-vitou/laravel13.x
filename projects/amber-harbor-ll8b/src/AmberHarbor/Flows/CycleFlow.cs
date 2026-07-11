namespace AmberHarbor.Flows;

public static class CycleFlow
{
  public static int Run(FarmContext ctx, string caption = "amber-harbor cycle #tiktok", string? video = null, bool skipVpn = false)
  {
    Console.WriteLine("=== 0/3 preflight ===");
    if (PreflightFlow.Run(ctx, skipVpn) != 0)
    {
      Console.WriteLine("cycle stopped at preflight");
      return 1;
    }

    Console.WriteLine("=== 1/3 signup ===");
    var (signupCode, email, password) = SignupFlow.Run(ctx);
    if (signupCode != 0)
    {
      Console.WriteLine("cycle stopped at signup");
      return 1;
    }

    Console.WriteLine("=== 2/3 login ===");
    if (LoginFlow.Run(ctx, email, password) != 0)
    {
      Console.WriteLine("cycle stopped at login");
      return 1;
    }

    Console.WriteLine("=== 3/3 post ===");
    if (PostFlow.Run(ctx, email, video, caption, skipLogin: true) != 0)
    {
      Console.WriteLine("cycle stopped at post");
      return 1;
    }

    Console.WriteLine("cycle: success (signup + login + post via LDPlayer)");
    return 0;
  }
}
