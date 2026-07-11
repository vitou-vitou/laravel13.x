namespace AmberHarbor.Flows;

public sealed class FarmContext
{
  public required Core.FarmSettings Settings { get; init; }
  public required int Index { get; init; }
  public required Core.LdPlayer Ld { get; init; }
  public required Core.AdbClient Adb { get; init; }
  public required Core.UiAutomator Ui { get; init; }
  public required Core.OtpFetcher Otp { get; init; }
  public Action<string>? Log { get; init; }

  public Core.VpnSettings Vpn => Settings.Fleet.Vpn;

  public static FarmContext Create(Action<string>? log = null, int? index = null)
  {
    var settings = Core.FarmSettings.Load();
    var idx = index ?? settings.LdPlayerIndex;
    var ld = new Core.LdPlayer(settings, log, idx);
    var adb = new Core.AdbClient(ld, log);
    var ui = new Core.UiAutomator(adb, log);
    var otp = new Core.OtpFetcher(settings, log);
    return new FarmContext
    {
      Settings = settings,
      Index = idx,
      Ld = ld,
      Adb = adb,
      Ui = ui,
      Otp = otp,
      Log = log,
    };
  }

  public Core.VpnOrchestrator VpnOrchestrator() =>
    new(Ld, Adb, Ui, Vpn, Log);

  public Core.LocationVerifier LocationVerifier() =>
    new(Adb, Vpn, Log);
}
