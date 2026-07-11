namespace VelvetForge.Mlbb;

public static class HeroCatalog
{
  public static readonly string[] Popular =
  [
    "Layla", "Miya", "Eudora", "Alucard", "Tigreal", "Franco", "Bruno", "Clint",
    "Saber", "Zilong", "Bane", "Akai", "Nana", "Rafaela", "Balmond", "Freya",
    "Gusion", "Lancelot", "Angela", "Kagura", "Chou", "Harley", "Fanny", "Grock",
  ];

  public static bool IsKnown(string name) =>
    Popular.Any(h => h.Equals(name, StringComparison.OrdinalIgnoreCase));
}
