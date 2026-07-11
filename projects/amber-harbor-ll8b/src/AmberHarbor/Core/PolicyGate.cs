namespace AmberHarbor.Core;

public static class PolicyGate
{
  public const string AckFlag = "--ack-research-only";
  public const string AckEnv = "TIKTOK_RESEARCH_ACK";

  public static bool HasResearchAck(string[] args)
  {
    if (args.Contains(AckFlag, StringComparer.OrdinalIgnoreCase)) return true;
    var env = Environment.GetEnvironmentVariable(AckEnv);
    return env is "1" or "true" or "yes";
  }

  public static void RequireResearchAck(string[] args)
  {
    if (HasResearchAck(args)) return;
    throw new InvalidOperationException(
      "Research boundary: signup/cycle require --ack-research-only or TIKTOK_RESEARCH_ACK=1. " +
      "See .agents/skills/tiktok-platform-policy-boundary/SKILL.md");
  }
}
