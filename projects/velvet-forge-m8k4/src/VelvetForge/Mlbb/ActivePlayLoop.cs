using VelvetForge.Core;
using VelvetForge.Flows;

namespace VelvetForge.Mlbb;

public sealed class ActivePlayLoop(FarmContext ctx)
{
  private readonly FarmContext _ctx = ctx;
  private readonly Random _rng = new();

  public int Run(int durationSeconds)
  {
    _ctx.Log?.Invoke($"mlbb: active play for {durationSeconds}s");
    var end = DateTimeOffset.UtcNow.AddSeconds(durationSeconds);
    var ticks = 0;
    var skillRotation = new[] { GameLayout.Skill1, GameLayout.Skill2, GameLayout.Skill3, GameLayout.Ultimate };

    while (DateTimeOffset.UtcNow < end)
    {
      MoveJoystick();
      _ctx.Input.TapScale(GameLayout.Attack.x, GameLayout.Attack.y);

      if (ticks % 3 == 0)
      {
        var skill = skillRotation[ticks % skillRotation.Length];
        _ctx.Input.TapScale(skill.x, skill.y);
      }

      if (ticks % 20 == 0)
        _ctx.Screenshot($"play-{ticks:D4}");

      ticks++;
      Thread.Sleep(_rng.Next(900, 1400));
    }

    _ctx.Log?.Invoke($"mlbb: active play complete ({ticks} ticks)");
    AppendPlayLog(ticks, durationSeconds);
    return ticks;
  }

  private void MoveJoystick()
  {
    var (jx, jy) = GameLayout.Joystick;
    var angle = _rng.NextDouble() * Math.PI * 2;
    var dist = 0.08 + _rng.NextDouble() * 0.10;
    var tx = jx + Math.Cos(angle) * dist;
    var ty = jy + Math.Sin(angle) * dist;
    _ctx.Input.SwipeScale(jx, jy, tx, ty);
  }

  private void AppendPlayLog(int ticks, int durationSeconds)
  {
    FarmPaths.EnsureDataDirs();
    var line = System.Text.Json.JsonSerializer.Serialize(new
    {
      ts = DateTimeOffset.UtcNow.ToString("o"),
      hero = _ctx.Settings.HeroName,
      ticks,
      durationSeconds,
      backend = _ctx.Input.ActiveBackend.ToString(),
    });
    File.AppendAllText(FarmPaths.PlayLogFile, line + Environment.NewLine);
  }
}
