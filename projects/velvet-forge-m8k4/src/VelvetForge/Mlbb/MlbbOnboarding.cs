using VelvetForge.Flows;

namespace VelvetForge.Mlbb;

public sealed class MlbbOnboarding(FarmContext ctx)
{
  private readonly FarmContext _ctx = ctx;

  public bool PassThrough()
  {
    _ctx.Log?.Invoke("mlbb: onboarding pass-through");
    for (var step = 0; step < 10; step++)
    {
      _ctx.SyncScreen();
      _ctx.Screenshot($"onboard-{step:D2}");

      if (_ctx.Ui.Available && (_ctx.Ui.HasText("Classic") || _ctx.Ui.HasText("BATTLE") || _ctx.Ui.HasText("Battle")))
      {
        _ctx.Log?.Invoke("mlbb: main lobby detected");
        return true;
      }

      _ctx.Log?.Invoke("mlbb: tap create-okay + tutorial confirms");
      _ctx.Input.TapScale(0.60, 0.62);
      Thread.Sleep(2500);
      _ctx.Input.TapScale(0.50, 0.88);
      Thread.Sleep(1500);
      _ctx.Input.TapScale(0.72, 0.88);
      Thread.Sleep(1500);
      _ctx.Input.TapScale(0.50, 0.50);
      Thread.Sleep(1200);

      if (step == 3 || step == 7)
      {
        _ctx.Log?.Invoke("mlbb: back from system overlay");
        _ctx.Input.Back();
        Thread.Sleep(1000);
      }
    }

    _ctx.SyncScreen();
    var ok = _ctx.Ui.Available && (_ctx.Ui.HasText("Classic") || _ctx.Ui.HasText("Battle"));
    _ctx.Log?.Invoke(ok ? "mlbb: onboarding done" : "mlbb: onboarding incomplete — continuing with coords");
    return ok;
  }
}
