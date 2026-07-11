namespace VelvetForge.Flows;

public static class CycleFlow
{
  public static int Run(FarmContext ctx, string? hero = null)
  {
    ctx.Log?.Invoke("==> cycle (preflight → play-hero)");
    return PlayHeroFlow.Run(ctx, hero);
  }
}
