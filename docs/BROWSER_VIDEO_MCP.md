# Browser + video testing (Cursor MCP)

How to visually test Laravel examples (e.g. **marketplace-v2**) with screenshots or recorded video.  
**Browse URL:** `http://<slug>.test` from `.env` `APP_URL` — not `:5173` unless debugging Vite only.

See also: [`MCP_SERVERS.md`](MCP_SERVERS.md) · [`GITHUB_UI_RESOURCE_INDEX.md`](GITHUB_UI_RESOURCE_INDEX.md) · [`EXAMPLE_DEV_LESSONS.md`](EXAMPLE_DEV_LESSONS.md)

---

## What you already have (no install)

| Tool | Video? | Use for |
|------|--------|---------|
| **Cursor Browser MCP** (`cursor-ide-browser`) | Screenshots only | Navigate, mobile viewport, full-page capture, visual QA |
| **PHPUnit** (`php artisan test`) | No | Routes, filters, cart — behavior assertions |
| **Mainframe share-video** | AI narrated recap | Async handoffs — not live browser capture |

**Prompt (built-in browser):**

```text
Open http://marketplace-v2.test at 390px width, full-page screenshot the catalog feed.
```

---

## Recommended GitHub MCPs for video

### 1. Microsoft Playwright MCP (WebM recordings)

- Repo: [microsoft/playwright-mcp](https://github.com/microsoft/playwright-mcp)
- Docs: [Video recording](https://playwright.dev/mcp/tools/video)
- Tools: `browser_start_video`, `browser_video_chapter`, `browser_stop_video`, `browser_video_show_actions`

**Cursor:** Settings → MCP → Add server (or use the “Install in Cursor” button on the repo README):

```json
{
  "mcpServers": {
    "playwright": {
      "command": "npx",
      "args": ["-y", "@playwright/mcp@latest", "--caps=devtools"]
    }
  }
}
```

**One-time:**

```bash
npx playwright install chromium
```

**Optional — record every session:**

```json
{
  "mcpServers": {
    "playwright": {
      "command": "npx",
      "args": ["-y", "@playwright/mcp@latest", "--caps=devtools"],
      "env": {
        "PLAYWRIGHT_MCP_SAVE_VIDEO": "390x844"
      }
    }
  }
}
```

**Example prompt:**

```text
Playwright MCP: start video, open http://marketplace-v2.test at 390×844,
scroll the catalog, browser_video_chapter title="Taobao feed", stop video.
```

---

### 2. Pagecast (GIF / MP4 demos)

- Repo: [mcpware/pagecast](https://github.com/mcpware/pagecast)
- Tools: `record_page`, `interact_page`, `stop_recording`, `convert_to_gif`, `record_and_export`

```json
{
  "mcpServers": {
    "pagecast": {
      "command": "npx",
      "args": ["-y", "@mcpware/pagecast"]
    }
  }
}
```

```bash
npx playwright install chromium
```

**Example prompt:**

```text
Pagecast record_and_export http://marketplace-v2.test as GIF, mobile platform preset.
```

---

### 3. Other options

| Source | Best for |
|--------|----------|
| [browser_mcp](https://github.com/Yukendiran2002/browser_mcp) | Large Playwright tool surface + video flag |
| **BrowserStack** (Cursor plugin) | Cross-browser test runs in cloud |
| **Browserbase Functions** (`functions` plugin) | Scheduled / webhook automation in cloud |

---

## Relevant agent skills (local)

| Skill | Trigger / path | Role |
|-------|----------------|------|
| **agent-browser** | `d:\laravel13.x\.claude\skills\agent-browser\` | CLI browser + dogfood QA; `npm i -g agent-browser && agent-browser install` |
| **playwright** | `~/.codex/skills/playwright/` | `playwright-cli` terminal automation |
| **playwright-pro** | `~/.agents/skills/playwright-pro/` | Generate/fix Playwright test suites |
| **demo-video** | `~/.agents/skills/demo-video/` | Demo videos via Playwright + ffmpeg + TTS |
| **impeccable** / **design-taste-frontend** | UI polish passes | Audit from screenshots/code |

Storefront inspiration (Taobao-tier density): `examples/marketplace-v2/docs/DESIGN.md` § Mobile app UX.

---

## marketplace-v2 quick matrix

| Goal | Tool |
|------|------|
| “Does the catalog look right?” | Cursor Browser MCP → screenshot |
| WebM walkthrough / bug repro | Playwright MCP + `--caps=devtools` |
| GIF for README | Pagecast |
| Regression safety | `export PATH="/d/laravel13.x/bin:$PATH"` → `cd examples/marketplace-v2 && php artisan test` |
| Verify Herd + tests | `./bin/verify-example marketplace-v2` |

**Example end-to-end prompt:**

```text
Video-test marketplace-v2 catalog: Playwright MCP, 390px wide,
http://marketplace-v2.test — record scroll through "For you" grid, save WebM.
Then confirm php artisan test still passes.
```

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| MCP tools missing after edit | Reload MCP in Cursor; restart if needed |
| `playwright` browser not found | `npx playwright install chromium` |
| Site 404 | `herd link marketplace-v2`; use `APP_URL` from `examples/marketplace-v2/.env` |
| Stale CSS | `cd examples/marketplace-v2 && npm run build` (browse still on `.test`) |
| `file://` in Simple Browser | Use `http://marketplace-v2.test`, not local HTML files |

---

## Do not

- Commit `.env` or API keys when configuring cloud MCPs
- Replace PHPUnit with video-only QA — keep both
- Clone third-party app branding when using Mobbin/UI Notes as inspiration (patterns only)
