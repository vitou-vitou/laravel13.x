using System.Text.RegularExpressions;
using MailKit.Net.Imap;
using MailKit.Search;

namespace AmberHarbor.Core;

public sealed class OtpFetcher(FarmSettings settings, Action<string>? log = null)
{
  private static readonly Regex CodeRe = new(@"\b(\d{6})\b", RegexOptions.Compiled);

  private void Log(string msg)
  {
    log?.Invoke(msg);
    Console.Error.WriteLine(msg);
  }

  public string? Poll(string recipient, long sinceEpoch, int maxWaitSeconds = 120, int pollSeconds = 8)
  {
    Log($"Polling OTP for {recipient} (max {maxWaitSeconds}s)...");
    var elapsed = 0;
    while (elapsed < maxWaitSeconds)
    {
      if (TryGws() is { } gws) return gws;
      if (TryImap(recipient, sinceEpoch) is { } imap) return imap;
      Thread.Sleep(pollSeconds * 1000);
      elapsed += pollSeconds;
    }
    return null;
  }

  private string? TryGws()
  {
    var repoRoot = FindRepoRoot();
    var candidates = new[]
    {
      Path.Combine(repoRoot, "bin", "gmail-tiktok-code.cmd"),
      Path.Combine(repoRoot, "bin", "gmail-tiktok-code"),
    };
    var script = candidates.FirstOrDefault(File.Exists);
    if (script is null) return null;

    try
    {
      var psi = new System.Diagnostics.ProcessStartInfo(script)
      {
        RedirectStandardOutput = true,
        UseShellExecute = false,
        CreateNoWindow = true,
      };
      using var p = System.Diagnostics.Process.Start(psi)!;
      var code = p.StandardOutput.ReadToEnd().Trim();
      p.WaitForExit(30_000);
      if (p.ExitCode == 0 && CodeRe.IsMatch(code)) return CodeRe.Match(code).Groups[1].Value;
    }
    catch { /* ignore */ }
    return null;
  }

  private string? TryImap(string recipient, long sinceEpoch)
  {
    if (string.IsNullOrWhiteSpace(settings.GmailPass)) return null;
    try
    {
      using var client = new ImapClient();
      client.Connect("imap.gmail.com", 993, true);
      client.Authenticate(settings.FullGmailAddress, settings.GmailPass);

      foreach (var boxName in new[] { "INBOX", "[Gmail]/Spam", "[Gmail]/All Mail" })
      {
        MailKit.IMailFolder folder;
        try { folder = client.GetFolder(boxName); }
        catch { continue; }
        try { folder.Open(MailKit.FolderAccess.ReadOnly); }
        catch { continue; }

        var uids = folder.Search(SearchQuery.All);
        foreach (var uid in uids.Reverse().Take(30))
        {
          var msg = folder.GetMessage(uid);
          var to = (msg.To.ToString() ?? "") + (msg.Headers["Delivered-To"] ?? "");
          if (!to.Contains(recipient, StringComparison.OrdinalIgnoreCase)) continue;
          var from = msg.From.ToString();
          if (!Regex.IsMatch(from, @"tiktok|account\.tiktok", RegexOptions.IgnoreCase)) continue;
          if (msg.Date.ToUnixTimeSeconds() < sinceEpoch - 5) continue;

          var body = msg.TextBody ?? msg.HtmlBody ?? "";
          var m = CodeRe.Match(body);
          if (m.Success && m.Groups[1].Value != "202510") return m.Groups[1].Value;
        }
      }
      client.Disconnect(true);
    }
    catch (Exception ex)
    {
      Log($"imap: {ex.Message}");
    }
    return null;
  }

  private static string FindRepoRoot()
  {
    var dir = FarmPaths.ProjectRoot;
    for (var i = 0; i < 8; i++)
    {
      if (Directory.Exists(Path.Combine(dir, "bin")) && Directory.Exists(Path.Combine(dir, "tools")))
        return dir;
      var parent = Directory.GetParent(dir)?.FullName;
      if (parent is null) break;
      dir = parent;
    }
    return FarmPaths.ProjectRoot;
  }
}
