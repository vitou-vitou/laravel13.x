# OCP Vision Bridge (POC)

Sidecar project to add **screenshot / image** support for Cursor when using [OCP](../ocp/README.md). OCP’s `/v1/chat/completions` path is **text-only** (`[non-text content omitted]` for images). This POC uses the **same Claude subscription OAuth** as OCP’s usage probe to call `api.anthropic.com/v1/messages` with vision blocks.

**Phase 1:** OAuth vision POC (`scripts/poc-vision.mjs`) — verified.  
**Phase 2:** HTTP sidecar (`server.mjs` on **:3457**) — routes images to Anthropic, text to OCP **:3456**.

## Prerequisites

- Node.js 22+
- `claude auth login` (same as OCP)
- Credentials at `%USERPROFILE%\.claude\.credentials.json` (Windows) or `~/.claude/.credentials.json`

## Run POC

```bash
cd ocp-vision-bridge
node scripts/poc-vision.mjs "C:\path\to\screenshot.png"
```

Optional env:

| Variable | Default | Purpose |
|----------|---------|---------|
| `OCP_VISION_MODEL` | `claude-haiku-4-5-20251001` | Anthropic model id |
| `OCP_VISION_FALLBACK_MODEL` | `aliases.haiku` in models.json | Retry model when Opus/Sonnet vision hits 429 |
| `OCP_VISION_FALLBACK_ON_429` | `1` (on) | Set `0` to disable Haiku fallback on rate limit |
| `POC_HINTS` | (none) | Comma-separated substrings that must appear in the response (smoke test) |
| `CLAUDE_CODE_OAUTH_TOKEN` | (from file) | Override access token |

Example with hints (API keys dashboard screenshot):

```bash
POC_HINTS="api keys,active" node scripts/poc-vision.mjs screenshot.png
```

## Pass / fail

| Result | Meaning |
|--------|---------|
| Exit 0 + description | Vision via subscription OAuth works — Phase 2 is viable |
| 401 / no credentials | Run `claude auth login` |
| 403 / image not supported | Stop sidecar; use Cursor native models for images |

### Verified (2026-06-09)

```bash
cd ocp-vision-bridge
node scripts/poc-vision.mjs ../ocp/runtime/docs/images/dashboard.png
# Exit 0 — Haiku described OCP dashboard screenshot via OAuth /v1/messages
```

## OpenSpec

Change proposal: [`openspec/changes/add-vision-poc/`](openspec/changes/add-vision-poc/proposal.md)

## Run sidecar (Phase 2)

```text
1. ocp\start.bat          → OCP on :3456
2. ocp-vision-bridge\start.bat  → bridge on :3457
3. Cursor Base URL → http://127.0.0.1:3457/v1  (or cloudflared tunnel to 3457, not 3456)
```

Test:

```bash
cd ocp-vision-bridge
npm run test:bridge
```

E2E with **agent-browser** (capture OCP dashboard → POST to bridge):

```bash
npm run test:agent-browser
# optional: custom URL, hints, bridge port
OCP_DASHBOARD_URL=http://127.0.0.1:3456/dashboard POC_HINTS=dashboard,api npm run test:agent-browser
```

Requires `agent-browser` on PATH (`npm i -g agent-browser && agent-browser install`).

## Related

- [OCP README](../ocp/README.md)
- [SESSION_STATE.md](../docs/SESSION_STATE.md) — OCP setup on new PCs
