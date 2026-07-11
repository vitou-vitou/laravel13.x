import { execFile } from "node:child_process";
import { promisify } from "node:util";
import { ImapFlow } from "imapflow";
import type { Settings } from "./config.js";
import { GMAIL_CODE_BIN } from "./paths.js";

const execFileAsync = promisify(execFile);
const CODE_RE = /\b(\d{6})\b/;
const REJECT_CODES = new Set(["202510"]);
const IMAP_TIMEOUT_MS = 12_000;

function accountEmail(settings: Settings): string {
  return `${settings.email}@${settings.eMailEnd}`;
}

function normalizeEmail(s: string): string {
  return s.trim().toLowerCase();
}

function messageRecipients(envelope: {
  to?: { address?: string }[];
  cc?: { address?: string }[];
}): string[] {
  const rows = [...(envelope.to ?? []), ...(envelope.cc ?? [])];
  return rows.map((r) => normalizeEmail(r.address ?? "")).filter(Boolean);
}

async function withTimeout<T>(promise: Promise<T>, ms: number): Promise<T | null> {
  try {
    return await Promise.race([
      promise,
      new Promise<null>((resolve) => setTimeout(() => resolve(null), ms)),
    ]);
  } catch {
    return null;
  }
}

export async function fetchOtpGwsOnce(): Promise<string | null> {
  try {
    const { stdout } = await execFileAsync(GMAIL_CODE_BIN, ["--once"], {
      timeout: 45_000,
      windowsHide: true,
    });
    return stdout.trim().match(CODE_RE)?.[1] ?? null;
  } catch {
    return null;
  }
}

async function fetchOtpImapOnce(
  settings: Settings,
  recipient: string,
  sinceEpoch: number,
): Promise<string | null> {
  const client = new ImapFlow({
    host: "imap.gmail.com",
    port: 993,
    secure: true,
    auth: { user: accountEmail(settings), pass: settings.gmailPass },
    logger: false,
  });

  client.on("error", () => {});

  const want = normalizeEmail(recipient);
  const since = new Date(sinceEpoch * 1000);

  try {
    await client.connect();
    const candidates: { date: Date; code: string }[] = [];

    for (const box of ["INBOX", "[Gmail]/Spam", "[Gmail]/All Mail"]) {
      try {
        await client.mailboxOpen(box);
      } catch {
        continue;
      }

      let uids: number[] = [];
      try {
        const found = await client.search({
          since,
          or: [{ from: "account.tiktok" }, { from: "tiktok" }],
        });
        uids = Array.isArray(found) ? found : [];
      } catch {
        const found = await client.search({ since });
        uids = Array.isArray(found) ? found : [];
      }

      if (!uids.length) continue;

      for await (const msg of client.fetch(uids, { envelope: true, source: true })) {
        const from = msg.envelope?.from?.[0]?.address ?? "";
        if (!/tiktok|account\.tiktok/i.test(from)) continue;

        const recips = messageRecipients(msg.envelope ?? {});
        if (
          recips.length &&
          !recips.some((r) => r === want || r.includes(want.split("@")[0]!))
        ) {
          continue;
        }

        const msgDate = msg.envelope?.date ?? new Date(0);
        if (msgDate.getTime() / 1000 < sinceEpoch - 5) continue;

        const body = msg.source?.toString("utf8") ?? "";
        const code = body.match(CODE_RE)?.[1];
        if (!code || REJECT_CODES.has(code)) continue;
        candidates.push({ date: msgDate, code });
      }
    }

    if (!candidates.length) return null;
    candidates.sort((a, b) => b.date.getTime() - a.date.getTime());
    return candidates[0]!.code;
  } catch {
    return null;
  } finally {
    await client.logout().catch(() => {});
  }
}

export async function fetchOtpImap(
  settings: Settings,
  recipient: string,
  sinceEpoch: number,
): Promise<string | null> {
  return (
    (await withTimeout(
      fetchOtpImapOnce(settings, recipient, sinceEpoch),
      IMAP_TIMEOUT_MS,
    )) ?? null
  );
}

export async function fetchOtp(
  settings: Settings,
  recipient: string,
  sinceEpoch: number,
): Promise<string | null> {
  const imap = await fetchOtpImap(settings, recipient, sinceEpoch);
  if (imap) return imap;
  return fetchOtpGwsOnce();
}
