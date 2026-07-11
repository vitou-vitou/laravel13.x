namespace AmberHarbor.Core;

public sealed record LdInstanceInfo(
  int Index,
  string Name,
  int TopWindowHandle,
  int BindWindowHandle,
  int AndroidStatus,
  int ProcessId,
  int VboxPid,
  int Width,
  int Height,
  int Dpi);
