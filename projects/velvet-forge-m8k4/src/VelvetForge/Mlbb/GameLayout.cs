namespace VelvetForge.Mlbb;

/// <summary>
/// Normalized UI coordinates (0..1) for MLBB landscape 1600x900 on LDPlayer.
/// Tuned for classic mode → hero pick → in-match active play.
/// </summary>
public static class GameLayout
{
  public static readonly (double x, double y) MainClassic = (0.50, 0.72);
  public static readonly (double x, double y) MainStart = (0.50, 0.88);
  public static readonly (double x, double y) ModeClassic = (0.50, 0.55);
  public static readonly (double x, double y) ModeStart = (0.50, 0.90);
  public static readonly (double x, double y) HeroSearch = (0.92, 0.08);
  public static readonly (double x, double y) HeroSearchField = (0.50, 0.10);
  public static readonly (double x, double y) HeroFirstResult = (0.50, 0.28);
  public static readonly (double x, double y) HeroLock = (0.88, 0.92);
  public static readonly (double x, double y) HeroConfirm = (0.50, 0.92);

  public static readonly (double x, double y) Joystick = (0.18, 0.78);
  public static readonly (double x, double y) Attack = (0.88, 0.82);
  public static readonly (double x, double y) Skill1 = (0.78, 0.78);
  public static readonly (double x, double y) Skill2 = (0.84, 0.68);
  public static readonly (double x, double y) Skill3 = (0.90, 0.58);
  public static readonly (double x, double y) Ultimate = (0.76, 0.58);

  public static readonly string[] DismissPatterns =
  [
    "Close", "OK", "Got it", "Skip", "Later", "Cancel", "Confirm", "Agree", "Accept",
  ];
}
