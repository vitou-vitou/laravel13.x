using VelvetForge.Mlbb;

namespace VelvetForge.Flows;

public static class PlayHeroFlow
{
  public static int Run(FarmContext ctx, string? heroOverride = null)
  {
    var hero = heroOverride ?? ctx.Settings.HeroName;
    ctx.Log?.Invoke($"==> play-hero {hero}");

    if (!HeroCatalog.IsKnown(hero))
      ctx.Log?.Invoke($"warn: {hero} not in catalog — continuing anyway");

    if (PreflightFlow.Run(ctx) != 0)
      return 1;

    var nav = new MlbbNavigator(ctx);
    nav.LaunchGame();

    if (!nav.NavigateToHeroSelect())
    {
      ctx.Screenshot("play-nav-fail");
      Console.WriteLine("play-hero: hero_select_unreachable");
      return 1;
    }

    nav.SelectHero(hero);
    nav.WaitForMatchStart();

    var loop = new ActivePlayLoop(ctx);
    var ticks = loop.Run(Math.Min(ctx.Settings.PlayDurationSeconds, 600));

    ctx.Screenshot("play-done");
    Console.WriteLine($"play-hero: ok hero={hero} ticks={ticks} backend={ctx.Input.ActiveBackend}");
    return ticks > 0 ? 0 : 1;
  }
}
