using VelvetForge.Core;
using VelvetForge.Flows;

namespace VelvetForge.Mlbb;

public sealed class MlbbNavigator(FarmContext ctx)
{
  private readonly FarmContext _ctx = ctx;

  public void LaunchGame()
  {
    _ctx.Log?.Invoke($"mlbb: launching {_ctx.Settings.MlbbPackage}");
    _ctx.Ld.KillApp();
    Thread.Sleep(1500);
    _ctx.Ld.RunApp();
    Thread.Sleep(8000);
    _ctx.Screenshot("launch-after-runapp");

    if (!LooksLikeMlbb())
    {
      _ctx.Log?.Invoke("mlbb: runapp missed — tapping launcher icon");
      TapLauncherIcon();
      Thread.Sleep(12000);
      _ctx.Screenshot("launch-after-icon");
    }

    DismissPopups();
    _ctx.Screenshot("launch-ready");

    new MlbbOnboarding(_ctx).PassThrough();
  }

  bool LooksLikeMlbb()
  {
    if (_ctx.Ui.Available)
      return _ctx.Ui.HasText("Mobile Legends") || _ctx.Ui.HasText("Classic") || _ctx.Ui.HasText("MOONTON");
    return false;
  }

  void TapLauncherIcon()
  {
    var portrait = _ctx.Settings.ScreenHeight > _ctx.Settings.ScreenWidth;
    if (portrait)
    {
      _ctx.Input.TapScale(0.80, 0.38);
      Thread.Sleep(1500);
      _ctx.Input.TapScale(0.80, 0.38);
    }
    else
    {
      _ctx.Input.TapScale(0.72, 0.22);
    }
  }

  public void DismissPopups()
  {
    if (_ctx.Ui.Available && _ctx.Ui.TapAny(_ctx.Input, GameLayout.DismissPatterns))
      return;
    _ctx.Input.TapScale(0.50, 0.88);
  }

  public bool NavigateToHeroSelect()
  {
    _ctx.Log?.Invoke("mlbb: navigate → classic match");
    new MlbbOnboarding(_ctx).PassThrough();
    DismissPopups();

    _ctx.Input.TapScale(0.50, 0.88);
    Thread.Sleep(2500);
    DismissPopups();

    if (_ctx.Ui.Available)
    {
      if (_ctx.Ui.TapAny(_ctx.Input, "Classic", "CLASSIC", "经典"))
        Thread.Sleep(3000);
      else if (_ctx.Ui.TapAny(_ctx.Input, "Battle", "Play", "Start"))
        Thread.Sleep(3000);
    }
    else
    {
      _ctx.Input.TapScale(GameLayout.ModeClassic.x, GameLayout.ModeClassic.y);
      Thread.Sleep(2000);
    }

    DismissPopups();
    _ctx.Input.TapScale(GameLayout.MainClassic.x, GameLayout.MainClassic.y);
    Thread.Sleep(2500);

    if (_ctx.Ui.Available && _ctx.Ui.TapAny(_ctx.Input, "Start", "Confirm", "Match"))
      Thread.Sleep(4000);
    else
    {
      _ctx.Input.TapScale(GameLayout.ModeStart.x, GameLayout.ModeStart.y);
      Thread.Sleep(4000);
    }

    _ctx.Screenshot("hero-select-wait");
    if (_ctx.Ui.Available && _ctx.Ui.WaitText("Pick", 25))
    {
      _ctx.Log?.Invoke("mlbb: hero select screen detected (Pick)");
      return true;
    }
    if (_ctx.Ui.Available && _ctx.Ui.WaitText("Hero", 10))
    {
      _ctx.Log?.Invoke("mlbb: hero select screen detected (Hero)");
      return true;
    }

    _ctx.Log?.Invoke("mlbb: assuming hero select (coordinate fallback)");
    return true;
  }

  public bool SelectHero(string heroName)
  {
    _ctx.Log?.Invoke($"mlbb: select hero {heroName}");
    DismissPopups();

    if (_ctx.Ui.Available && _ctx.Ui.TapMatch(_ctx.Input, heroName))
    {
      Thread.Sleep(1500);
    }
    else
    {
      _ctx.Input.TapScale(GameLayout.HeroSearch.x, GameLayout.HeroSearch.y);
      Thread.Sleep(1200);
      _ctx.Input.TapScale(GameLayout.HeroSearchField.x, GameLayout.HeroSearchField.y);
      Thread.Sleep(800);
      _ctx.Input.Text(heroName);
      Thread.Sleep(1500);
      _ctx.Input.TapScale(GameLayout.HeroFirstResult.x, GameLayout.HeroFirstResult.y);
      Thread.Sleep(1500);
    }

    if (_ctx.Ui.Available && _ctx.Ui.TapAny(_ctx.Input, "Lock", "Confirm", "Pick"))
      Thread.Sleep(2000);
    else
    {
      _ctx.Input.TapScale(GameLayout.HeroLock.x, GameLayout.HeroLock.y);
      Thread.Sleep(1200);
      _ctx.Input.TapScale(GameLayout.HeroConfirm.x, GameLayout.HeroConfirm.y);
      Thread.Sleep(2000);
    }

    _ctx.Screenshot($"hero-{heroName.ToLowerInvariant()}");
    return true;
  }

  public bool WaitForMatchStart(int maxSeconds = 120)
  {
    _ctx.Log?.Invoke("mlbb: waiting for match start");
    for (var i = 0; i < maxSeconds; i++)
    {
      if (_ctx.Ui.Available && (_ctx.Ui.HasText("VS") || _ctx.Ui.HasText("Loading")))
        Thread.Sleep(5000);
      if (_ctx.Ui.Available && _ctx.Ui.HasText("Recall"))
      {
        _ctx.Log?.Invoke("mlbb: in-match (Recall visible)");
        return true;
      }
      if (i > 30 && i % 15 == 0)
        _ctx.Screenshot($"match-loading-{i}");
      Thread.Sleep(1000);
    }
    _ctx.Log?.Invoke("mlbb: match start timeout — proceeding to active play anyway");
    return true;
  }
}
