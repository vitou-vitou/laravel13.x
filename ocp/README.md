# OCP — Claude Pro in Cursor (Windows)

Use your **Claude Pro/Max** subscription inside **Cursor** via an OpenAI-compatible proxy. No extra API cost.

Everything lives in this folder. On a **new PC**, clone `laravel13.x`, run install once, then start daily.

## Quick start (new machine)

### 1. Prerequisites

| Tool | Install |
|------|---------|
| Node.js **22.5+** | https://nodejs.org |
| Git | https://git-scm.com |
| Cloudflared | https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/ |
| Claude Code CLI | `npm install -g @anthropic-ai/claude-code` |

Log in to Claude (each person uses their own subscription):

```powershell
claude auth login
claude auth status
```

### 2. Install (once per machine)

From this folder:

```text
install.bat
```

This will:

1. Clone [dtzp555-max/ocp](https://github.com/dtzp555-max/ocp) into `runtime/` (gitignored)
2. Run `npm install`
3. Apply Windows + Cursor patches automatically (`patch-windows.ps1`)

### 3. Start (each coding session)

```text
start.bat
```

1. Proxy listens on `http://127.0.0.1:3456`
2. Cloudflare prints a `https://….trycloudflare.com` URL — **keep this terminal open**
3. Copy URL + `/v1` into Cursor

### 4. Cursor settings

**Settings → Models → API Keys**

| Setting | Value |
|---------|--------|
| OpenAI API Key | ON — any string, e.g. `ocp-local` |
| Override OpenAI Base URL | ON — `https://YOUR-TUNNEL.trycloudflare.com/v1` |

In chat, pick a **GPT-5.x** model. See **`MODEL-MAP.txt`** for which Claude model runs.

### 5. Verify

```text
check.bat
```

Or manually: `curl http://127.0.0.1:3456/v1/models`

## Folder layout

```text
ocp/
  README.md           ← you are here
  MODEL-MAP.txt       ← Cursor name → Claude model
  install.bat         ← first-time setup
  start.bat           ← daily: proxy + tunnel
  check.bat           ← health check
  install.ps1
  start.ps1
  check.ps1
  patch-windows.ps1   ← idempotent Windows patches
  patches/
    cursor-legacy-aliases.json
  runtime/            ← cloned upstream (gitignored)
```

## Model mapping

| Cursor (GPT name) | Runs |
|-------------------|------|
| GPT-5.5 | Claude Opus 4.8 |
| GPT-5.4 | Claude Opus 4.7 |
| Codex 5.3 | Claude Opus 4.6 |
| GPT-5.2 / 5.4 Mini / 5.1 | Claude Sonnet 4.6 |
| GPT-5.4 Nano / 5 Mini | Claude Haiku 4.5 |

Higher GPT number ≈ stronger Claude.

## Notes

- **Tunnel URL changes** every `start.bat` — update Cursor Base URL each time (or set up a [named Cloudflare tunnel](https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/) for a stable URL).
- **One Claude login per developer** — team members each run `claude auth login` on their PC.
- **Re-install / update upstream:** run `install.bat` again (git pull + re-patch).
- Root shortcuts: `start-ocp.bat` in repo root calls `ocp/start.bat`.

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `runtime/ not installed` | Run `install.bat` |
| `claude not logged in` | `claude auth login` |
| Cursor 404 / connection refused | Re-copy tunnel URL + `/v1`; terminal must stay open |
| Long prompts fail on Windows | Re-run `install.bat` (applies prompt length patch) |
| Wrong model in Cursor | Use GPT-5.x names; see `MODEL-MAP.txt` |
