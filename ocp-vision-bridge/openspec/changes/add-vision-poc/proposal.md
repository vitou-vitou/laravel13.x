# Proposal: OCP Vision Bridge — Phase 1 POC

## Problem

Cursor + OCP cannot read attached images. OCP `contentToText()` strips `image_url` parts before spawning `claude` CLI.

## Scope (Phase 1)

- CLI script `scripts/poc-vision.mjs` sends a local image to `/v1/messages` using Claude Code OAuth.
- Reuse credential loading pattern from `ocp/runtime/server.mjs`.

## Out of scope

- HTTP sidecar proxy
- Cursor end-to-end integration
- Patching `ocp/runtime` upstream

## Acceptance criteria

1. `node scripts/poc-vision.mjs <png>` exits 0 with non-empty description. **Done** (2026-06-09, dashboard.png).
2. Optional `POC_HINTS` env validates expected UI text in response.
3. README documents prerequisites and kill criteria for Phase 2.

## Phase 2 (future change)

OpenSpec change `add-vision-sidecar-proxy`: listen `:3457`, route images to Anthropic API, text to OCP `:3456`.
