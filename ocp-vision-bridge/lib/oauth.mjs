import { readFileSync } from "node:fs";
import { homedir } from "node:os";
import { join } from "node:path";
import { execFileSync } from "node:child_process";

const OAUTH_CLIENT_ID = "9d1c250a-e61b-44d9-88ed-5944d1962f5e";
const OAUTH_TOKEN_URL = "https://platform.claude.com/v1/oauth/token";

export function getOAuthCredentials() {
  if (process.env.CLAUDE_CODE_OAUTH_TOKEN) {
    return { accessToken: process.env.CLAUDE_CODE_OAUTH_TOKEN };
  }

  try {
    const credPath = join(homedir(), ".claude", ".credentials.json");
    const creds = JSON.parse(readFileSync(credPath, "utf8"));
    if (creds?.claudeAiOauth?.accessToken) {
      return creds.claudeAiOauth;
    }
  } catch {
    // fall through
  }

  if (process.platform === "darwin") {
    for (const label of ["claude-code-credentials", "Claude Code-credentials"]) {
      try {
        const raw = execFileSync(
          "security",
          ["find-generic-password", "-s", label, "-w"],
          { encoding: "utf8", timeout: 5000 },
        ).trim();
        const creds = JSON.parse(raw);
        if (creds?.claudeAiOauth?.accessToken) {
          return creds.claudeAiOauth;
        }
      } catch {
        // try next
      }
    }
  }

  return null;
}

export async function refreshOAuthToken(refreshToken) {
  const resp = await fetch(OAUTH_TOKEN_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      grant_type: "refresh_token",
      refresh_token: refreshToken,
      client_id: OAUTH_CLIENT_ID,
      scope: "user:inference user:profile",
    }),
  });

  if (!resp.ok) {
    const body = await resp.text();
    throw new Error(`OAuth refresh failed ${resp.status}: ${body.slice(0, 300)}`);
  }

  const data = await resp.json();
  return data.access_token || null;
}

export async function getValidAccessToken() {
  const creds = getOAuthCredentials();
  if (!creds?.accessToken) {
    throw new Error(
      "No OAuth token. Run: claude auth login (or set CLAUDE_CODE_OAUTH_TOKEN)",
    );
  }

  let token = creds.accessToken;

  if (creds.expiresAt && Date.now() + 300_000 >= creds.expiresAt && creds.refreshToken) {
    const refreshed = await refreshOAuthToken(creds.refreshToken);
    if (refreshed) {
      token = refreshed;
    }
  }

  return token;
}
