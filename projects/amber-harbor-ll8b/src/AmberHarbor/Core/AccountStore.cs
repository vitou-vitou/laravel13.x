using System.Text.Json;
using System.Text.Json.Serialization;

namespace AmberHarbor.Core;

public sealed class FarmAccount
{
  [JsonPropertyName("email")] public string Email { get; set; } = "";
  [JsonPropertyName("password")] public string Password { get; set; } = "";
  [JsonPropertyName("createdAt")] public string CreatedAt { get; set; } = "";
  [JsonPropertyName("posts")] public int Posts { get; set; }
  [JsonPropertyName("lastLogin")] public string? LastLogin { get; set; }
  [JsonPropertyName("lastPost")] public string? LastPost { get; set; }
}

public static class AccountStore
{
  private static readonly JsonSerializerOptions JsonOpts = new() { WriteIndented = true };

  public static List<FarmAccount> Load()
  {
    FarmPaths.EnsureDataDirs();
    if (!File.Exists(FarmPaths.AccountsFile)) return [];
    try
    {
      var json = File.ReadAllText(FarmPaths.AccountsFile);
      return JsonSerializer.Deserialize<List<FarmAccount>>(json) ?? [];
    }
    catch
    {
      return [];
    }
  }

  private static void Save(List<FarmAccount> list) =>
    File.WriteAllText(FarmPaths.AccountsFile, JsonSerializer.Serialize(list, JsonOpts));

  public static void Upsert(string email, string password)
  {
    var list = Load();
    var now = DateTime.UtcNow.ToString("o");
    var i = list.FindIndex(a => a.Email.Equals(email, StringComparison.OrdinalIgnoreCase));
    if (i >= 0) list[i].Password = password;
    else list.Add(new FarmAccount { Email = email, Password = password, CreatedAt = now });
    Save(list);
  }

  public static FarmAccount? Latest()
  {
    var list = Load();
    return list.Count == 0 ? null : list[^1];
  }

  public static void MarkLogin(string email)
  {
    var list = Load();
    var acc = list.Find(a => a.Email.Equals(email, StringComparison.OrdinalIgnoreCase));
    if (acc is null) return;
    acc.LastLogin = DateTime.UtcNow.ToString("o");
    Save(list);
  }

  public static void MarkPost(string email)
  {
    var list = Load();
    var acc = list.Find(a => a.Email.Equals(email, StringComparison.OrdinalIgnoreCase));
    if (acc is null) return;
    acc.LastPost = DateTime.UtcNow.ToString("o");
    acc.Posts++;
    Save(list);
  }
}
