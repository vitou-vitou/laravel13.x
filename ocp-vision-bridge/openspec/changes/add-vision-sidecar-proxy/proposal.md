# Proposal: OCP Vision Bridge — Phase 2 sidecar proxy

## Depends on

Phase 1 POC (`add-vision-poc`) — verified OAuth vision works.

## Delivered

- `server.mjs` on `:3457` — forwards text to OCP `:3456`, images to Anthropic API
- `start.bat` / `start.ps1` — requires OCP running first
- `scripts/test-bridge-vision.mjs` — local integration test

## Cursor setup

1. `ocp\start.bat` (OCP on 3456)
2. `ocp-vision-bridge\start.bat` (bridge on 3457)
3. Cursor → Override OpenAI Base URL → `http://127.0.0.1:3457/v1` or cloudflared tunnel to **3457**

## Verified (2026-06-09)

```bash
ocp-vision-bridge/start.ps1
npm run test:bridge
# PASS — image via bridge :3457, text passthrough to OCP :3456
```

## Limitations

- Vision path buffers full response then SSE-chunks (no native Anthropic stream yet)
- Agent mode with images + huge history may hit token limits
- Windows OCP system-prompt 4k cap still applies on text passthrough
