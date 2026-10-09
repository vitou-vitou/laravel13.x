---
name: router-free-model-bench
description: Benchmark free-tier chat models on an OpenAI-compatible provider (unorouter, kktoken, or any 9router openai-compatible-chat-* connection) for speed/quality, and sync results to Postman. Use when the user asks to "test free models", "review model quality + speed", "benchmark this provider", or after debugging a provider connection (kktoken/unorouter/etc.) and wants results tracked.
---

# Free-Model Benchmark → Postman Sync

## When to use
- User gives a curl/API key for an OpenAI-compatible provider and asks to list/test/review free models.
- User asks to benchmark speed+quality of `:free` models on a router (unorouter, kktoken, OpenRouter-style APIs).
- Follow-up to a provider debugging session (e.g. a 403/503 investigation) where the user wants results saved for later reference.

## Pre-registered providers (config, not code)

`scripts/freeModelProviders.json` holds known providers with an `enabled` toggle — pure data, no script changes needed to add/enable/disable one:

```json
{
  "id": "unorouter",
  "enabled": true,
  "baseUrl": "https://api.unorouter.com/v1",
  "apiKeyEnv": "UNOROUTER_API_KEY",
  "postmanWorkspace": "9router-kktoken",
  "postmanWorkspaceId": "b33a21eb-...",
  "postmanCollectionId": "2608740-...",
  "notes": "free-form notes, last-known-good picks"
}
```

- **New provider the user mentions** → append an entry (`enabled: false` until they confirm they want it tracked, `enabled: true` if they explicitly ask to benchmark now).
- **User says "enable X" / "disable X"** → flip that provider's `enabled` field only. Don't touch other entries.
- **User says "test all" / "run the usual"** → run with `--all`, which loops every `enabled: true` provider automatically.
- Never put a real API key in this file — only the env-var *name* (`apiKeyEnv`). Ask the user to `export FOO_API_KEY=...` or pass it inline before running.

## Steps

1. **List free models**: `GET {baseUrl}/models`, filter `id` ending `:free`, `supported_endpoint_types` includes `openai`, excludes `embedding`, excludes `owned_by` in `["ai horde","runware"]` (image/audio-only backends). The script does this automatically.
2. **Run the benchmark script** (repo: `scripts/benchmarkFreeModels.mjs`):
   ```bash
   # one-off provider not yet in config:
   BASE_URL=<baseUrl> API_KEY=<key> node scripts/benchmarkFreeModels.mjs [--limit=N] [--prompt="..."]

   # all enabled providers from freeModelProviders.json:
   UNOROUTER_API_KEY=<key> KKTOKEN_API_KEY=<key> node scripts/benchmarkFreeModels.mjs --all [--limit=N]
   ```
   Outputs one markdown table per provider (model | HTTP | latency | note) to stdout, sorted fastest-first among successes.
   - Don't dump the raw API key into chat text back to the user — treat it as sensitive after first use.
   - A `429` with "free tier allows 1 request per minute" is expected on some providers when `--limit` is high — note it, don't treat as broken.
3. **Sync to Postman** (namespace `plugin-postman-postman`, needs `mcp_auth` once per session):
   - Find/create a workspace scoped to this investigation (reuse an existing one if the user already has one for this provider, e.g. `9router-kktoken`).
   - `createCollection` (or `patchCollection` if one already exists) with:
     - A `List Models` request (`GET {{baseUrl}}/models`)
     - A `Test - Any Free Model` request (`POST {{baseUrl}}/chat/completions`, body uses `{{model}}` variable)
     - Collection `variable`: `baseUrl`, `model` (prefilled to top pick), leave `apiKey` **empty** — never write the real key into Postman via MCP.
     - Collection `info.description`: paste the markdown benchmark table + a one-line "RECOMMENDED default" pick + timestamp.
   - Tell the user to set `apiKey` as a **secret**-type variable manually in the Postman UI (Variables tab → Type → secret → paste in Current Value) — MCP `patchCollection` cannot set variable type, only value.
4. **Report back**: short table in chat (top 3-5 by latency among HTTP 200s), one recommended default, and the Postman collection link.

## Linking to a 9router provider connection

This benchmarked API is not a static provider in `open-sse/config/providers.js` — it's used the same way as any OpenAI-compatible custom endpoint: a dynamic `openai-compatible-chat-*` connection created via the 9router dashboard (Providers → Add Connection → OpenAI-compatible, paste `baseUrl` + `apiKey`). No code change needed to "link" it; only add a static registry entry (`open-sse/providers/registry/{id}.js`, copy `REGISTRY_TEMPLATE.js`) if the user wants it as a first-class named provider in the UI provider list rather than an ad-hoc connection.

## Pitfalls
- `owned_by: "ai horde"` / `"runware"` entries are image models — filter them out or the benchmark wastes calls on 4xx.
- Some `:free` models return a `reasoning` field with leaked chain-of-thought (e.g. qwen3.x) — note this in the report, don't silently treat as bad output.
- 503 `get_channel_failed` / `model_not_found` on one model ≠ your key is broken — it's per-model upstream channel exhaustion on the router's side. Don't debug auth for this; just note and move on.
- Skip adding retries/concurrency to the benchmark script unless testing >50 models routinely — YAGNI.
