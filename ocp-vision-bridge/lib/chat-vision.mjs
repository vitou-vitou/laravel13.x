import { getValidAccessToken, refreshOAuthToken, getOAuthCredentials } from "./oauth.mjs";
import { openAiMessagesToAnthropic } from "./openai-content.mjs";
import { resolveAnthropicModel, getVisionFallbackModel } from "./model-map.mjs";

const MESSAGES_URL = "https://api.anthropic.com/v1/messages";

function isRateLimited(status) {
  return status === 429 || status === 529;
}

async function callAnthropicMessages({ model, maxTokens, system, anthropicMessages, token, creds }) {
  const body = {
    model,
    max_tokens: maxTokens,
    messages: anthropicMessages,
  };
  if (system) {
    body.system = system;
  }

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
  let parsedResp;
  try {
    parsedResp = JSON.parse(raw);
  } catch {
    throw new Error(`Non-JSON from Anthropic ${resp.status}: ${raw.slice(0, 400)}`);
  }

  return { resp, parsedResp, raw, token };
}

export async function chatCompletionsWithVision(parsed) {
  const messages = parsed.messages || [];
  const requestModel = parsed.model || "gpt-5.2";
  const anthropicModel = resolveAnthropicModel(requestModel);
  const fallbackModel = getVisionFallbackModel();
  const allowFallback = process.env.OCP_VISION_FALLBACK_ON_429 !== "0";
  const maxTokens = parsed.max_tokens || 4096;

  const { system, messages: anthropicMessages } = await openAiMessagesToAnthropic(messages);
  if (anthropicMessages.length === 0) {
    throw new Error("No user/assistant messages after conversion");
  }

  const creds = getOAuthCredentials();
  let token = await getValidAccessToken();

  const baseParams = { maxTokens, system, anthropicMessages, creds };
  let usedModel = anthropicModel;
  let visionFallback = false;

  let result = await callAnthropicMessages({ ...baseParams, model: usedModel, token });
  token = result.token;

  if (
    !result.resp.ok &&
    isRateLimited(result.resp.status) &&
    allowFallback &&
    usedModel !== fallbackModel
  ) {
    console.warn(
      `[vision-bridge] ${usedModel} rate-limited (${result.resp.status}), retrying with ${fallbackModel}`,
    );
    usedModel = fallbackModel;
    visionFallback = true;
    result = await callAnthropicMessages({ ...baseParams, model: usedModel, token });
  }

  const { resp, parsedResp, raw } = result;

  if (!resp.ok) {
    const msg = parsedResp?.error?.message || raw.slice(0, 400);
    throw new Error(`Anthropic ${resp.status}: ${msg}`);
  }

  const text = (parsedResp.content || [])
    .filter((b) => b.type === "text")
    .map((b) => b.text)
    .join("");

  return {
    text,
    model: requestModel,
    anthropicModel: usedModel,
    visionFallback,
    usage: parsedResp.usage || {},
  };
}
