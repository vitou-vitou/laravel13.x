namespace AmberHarbor.Core;

public static class FleetIndex
{
  public static int AdbPort(int index) => 5555 + index * 2;

  public static string AdbSerial(int index) => $"127.0.0.1:{AdbPort(index)}";

  public static string EmulatorSerial(int index) => $"emulator-{5554 + index * 2}";

  public static string InstanceName(int index) => index == 0 ? "LDPlayer" : $"farm-{index:D2}";
}
