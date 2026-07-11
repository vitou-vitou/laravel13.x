const TOS = "https://www.tiktok.com/legal/terms-of-service";

export const POLICY_SUMMARY = `
TikTok farm-ts — research boundary
----------------------------------
• Automated signup/posting violates TikTok ToS: ${TOS}
• Local research / education only — not production farming.
• Pass --ack-research-only or set TIKTOK_RESEARCH_ACK=1 for signup/cycle.

Allowed in repo: tools/tiktok-metadata (creator consent, metadata-only).
`.trim();

export function researchAcknowledged(argv: string[]): boolean {
  if (process.env.TIKTOK_RESEARCH_ACK === "1") return true;
  return argv.includes("--ack-research-only");
}

export function requireResearchAck(argv: string[]): void {
  if (!researchAcknowledged(argv)) {
    console.error(POLICY_SUMMARY);
    process.exit(2);
  }
}
