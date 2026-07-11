namespace AmberHarbor.Core;

public static class CliArgs
{
  public static int? GetIndex(string[] args)
  {
    var v = GetOpt(args, "--index");
    return v is not null && int.TryParse(v, out var n) ? n : null;
  }

  public static int GetIntOpt(string[] args, string name, int defaultValue)
  {
    var v = GetOpt(args, name);
    return v is not null && int.TryParse(v, out var n) ? n : defaultValue;
  }

  public static string? GetOpt(string[] args, string name)
  {
    for (var i = 0; i < args.Length - 1; i++)
      if (args[i].Equals(name, StringComparison.OrdinalIgnoreCase))
        return args[i + 1];
    return null;
  }

  public static bool HasFlag(string[] args, string name) =>
    args.Any(a => a.Equals(name, StringComparison.OrdinalIgnoreCase));

  public static string[] WithoutCommand(string[] args) =>
    args.Length <= 1 ? [] : args.Skip(1).ToArray();
}
