import { readFileSync } from "node:fs";
import { extname } from "node:path";
import { getValidAccessToken, refreshOAuthToken, getOAuthCredentials } from "./oauth.mjs";

const DEFAULT_MODEL = "claude-haiku-4-5-20251001";
const MESSAGES_URL = "https://api.anthropic.com/v1/messages";

function mediaTypeForPath(imagePath) {
  const ext = extname(imagePath).toLowerCase();
  if (ext === ".png") return "image/png";
  if (ext === ".jpg" || ext === ".jpeg") return "image/jpeg";
  if (ext === ".gif") return "image/gif";
  if (ext === ".webp") return "image/webp";
  throw new Error(`Unsupported image type: ${ext || "(no extension)"}`);
}

export function loadImageBlock(imagePath) {
  const mediaType = mediaTypeForPath(imagePath);
  const data = readFileSync(imagePath).toString("base64");
  return {
    type: "image",
    source: { type: "base64", media_type: mediaType, data },
  };
}

export async function describeImage(imagePath, options = {}) {
  const {
    model = process.env.OCP_VISION_MODEL || DEFAULT_MODEL,
    prompt = "Describe this screenshot in 3-5 sentences. Mention any visible UI text.",
    maxTokens = 1024,
  } = options;

  const creds = getOAuthCredentials();
  let token = await getValidAccessToken();
  const imageBlock = loadImageBlock(imagePath);

  const body = {
    model,
    max_tokens: maxTokens,
    messages: [
      {
        role: "user",
        content: [{ type: "text", text: prompt }, imageBlock],
      },
    ],
  };

  const doFetch = (bearer) =>
    fetch(MESSAGES_URL, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${bearer}`,
        "anthropic-version": "2023-06-01",
        "anthropic-beta": "oauth-2025-04-20",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(body),
    });

  let resp = await doFetch(token);

  if (resp.status === 401 && creds?.refreshToken) {
    const newToken = await refreshOAuthToken(creds.refreshToken);
    if (newToken) {
      token = newToken;
      resp = await doFetch(token);
    }
  }

  const raw = await resp.text();
  let parsed;
  try {
    parsed = JSON.parse(raw);
  } catch {
    throw new Error(`Non-JSON response ${resp.status}: ${raw.slice(0, 500)}`);
  }

  if (!resp.ok) {
    const msg = parsed?.error?.message || raw.slice(0, 500);
    throw new Error(`Messages API ${resp.status}: ${msg}`);
  }

  const text = (parsed.content || [])
    .filter((b) => b.type === "text")
    .map((b) => b.text)
    .join("");

  return { text, model, usage: parsed.usage };
}
