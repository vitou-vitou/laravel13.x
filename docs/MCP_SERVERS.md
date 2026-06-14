# Cursor MCP servers (laravel13.x)

Aligned stack for this monorepo: **docs + UI + browser QA + Laravel package docs**.  
Global config: `~/.cursor/mcp.json` (not committed — `.cursor/` is gitignored except `rules/`).

See also: [`BROWSER_VIDEO_MCP.md`](BROWSER_VIDEO_MCP.md) · [`GITHUB_UI_RESOURCE_INDEX.md`](GITHUB_UI_RESOURCE_INDEX.md)

---

## Installed servers (this machine)

| Server | GitHub | Role | When to use |
|--------|--------|------|-------------|
| **playwright** | [microsoft/playwright-mcp](https://github.com/microsoft/playwright-mcp) | Headless Chromium, snapshots, **WebM video** | `http://<slug>.test` mobile screenshots, recorded walkthroughs, PHPUnit follow-up |
| **browsermcp** | [BrowserMCP/mcp](https://github.com/BrowserMCP/mcp) | **Your Chrome** via extension (logged-in sessions) | Dogfood in real profile, OAuth sites, “test what I see in Chrome” |
| **@21st-dev/magic** | [21st-dev/magic-mcp](https://github.com/21st-dev/magic-mcp) | AI UI components (`/ui` in chat) | New React/Tailwind blocks for examples; pairs with `DESIGN.md` |
| **context7** | [upstash/context7](https://github.com/upstash/context7) | Version-specific library docs | Before Laravel/Livewire/Stripe changes — “use context7 for Laravel 13 …” |
| **cursor-ide-browser** | (Cursor built-in) | In-IDE browser tab | Quick snapshot when Playwright MCP not needed |
| **aikido** | — | Security scan on changed code | After generating/editing first-party code |
| **prompts.chat** | — | Prompt/skill registry | 21st/shadcn prompt templates |
| **superhuman-mail** | — | Email/calendar | Inbox workflows only |

### Division of labor (browser)

```
marketplace-v2.test QA
├── PHPUnit (required)     → behavior / regressions
├── playwright MCP         → mobile viewport, screenshots in repo, WebM demos
├── browsermcp             → your Chrome + extension (logged-in, manual connect)
└── cursor-ide-browser     → fast in-IDE checks
```

Do **not** duplicate all three browsers on one task — pick one per goal.

---

## API keys (required for full rate limits)

| Server | Get key | Config key |
|--------|---------|------------|
| 21st.dev Magic | [21st.dev/magic/console](https://21st.dev/magic/console) | `API_KEY` in `@21st-dev/magic` env |
| Context7 | [context7.com/dashboard](https://context7.com/dashboard) (optional) | `CONTEXT7_API_KEY` header |
| Aikido / prompts.chat | Already in global `mcp.json` | — |

Replace `YOUR_21ST_DEV_API_KEY` and `YOUR_CONTEXT7_API_KEY` in `~/.cursor/mcp.json`, then **reload MCP** in Cursor Settings.

Context7 works without a key at lower limits — you can remove the `headers` block until you add a key.

---

## Copy-paste: merge into `~/.cursor/mcp.json`

Keeps existing **playwright** (`--caps=devtools`) and adds the three GitHub servers. **Windows** Browser MCP uses `cmd /c npx`.

```json
{
  "mcpServers": {
    "playwright": {
      "command": "npx",
      "args": ["-y", "@playwright/mcp@latest", "--caps=devtools"]
    },
    "@21st-dev/magic": {
      "command": "npx",
      "args": ["-y", "@21st-dev/magic@latest"],
      "env": {
        "API_KEY": "YOUR_21ST_DEV_API_KEY"
      }
    },
    "browsermcp": {
      "command": "cmd",
      "args": ["/c", "npx", "-y", "@browsermcp/mcp@latest"],
      "env": {
        "CHROME_PATH": "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe"
      }
    },
    "context7": {
      "url": "https://mcp.context7.com/mcp",
      "headers": {
        "CONTEXT7_API_KEY": "YOUR_CONTEXT7_API_KEY"
      }
    }
  }
}
```

Merge with your other servers (`aikido`, `prompts.chat`, etc.) — do not replace the whole file unless intentional.

### CLI installers (alternative)

```bash
# 21st.dev — writes Cursor config + API key
npx @21st-dev/cli@latest install cursor --api-key <key>

# Context7 — MCP + skill + rule
npx ctx7 setup --cursor
```

### Browser MCP extension (required)

1. Install from [browsermcp.io](https://browsermcp.io/)
2. Open Chrome → extension → **Connect**
3. Reload **browsermcp** in Cursor MCP settings

---

## Example prompts (marketplace-v2)

**Playwright** (saved under `examples/marketplace-v2/docs/screenshots/`):

```text
Playwright MCP: 390×844, http://marketplace-v2.test, screenshot catalog as
examples/marketplace-v2/docs/screenshots/catalog.png
```

**21st.dev** (Flux/Tailwind component for catalog card):

```text
/ui compact mobile product card — price bold, vendor star rating, square image (match DESIGN.md Taobao pass)
```

**Context7** (before Stripe/Livewire edits):

```text
use context7 — Laravel 13 validation rules for nested cart line items
```

**Browser MCP** (logged-in vendor admin in your Chrome):

```text
Browser MCP: open http://marketplace-v2.test/vendor, confirm connect, screenshot dashboard
```

---

## Troubleshooting (Windows + Herd)

| Issue | Fix |
|-------|-----|
| browsermcp tools missing | Install Chrome extension + Connect; use `cmd /c npx` config above |
| browsermcp vs Cursor conflict | Only one client may own the extension — close other MCP browsers or disable duplicate |
| playwright “browser not found” | `npx playwright install chromium` |
| 21st `/ui` does nothing | Set `API_KEY` from Magic Console; reload MCP |
| context7 rate limit | Add free API key at context7.com/dashboard |
| Herd 404 | `herd link marketplace-v2`; browse `APP_URL` from `.env` |

---

## Per-example MCP (Laravel Boost)

Some examples ship their own `.cursor/mcp.json` (e.g. `laravel-boost` only). That **merges** with global servers — Boost for DB/schema, global for browser/UI/docs.

```json
{
  "mcpServers": {
    "laravel-boost": {
      "command": "php",
      "args": ["artisan", "boost:mcp"]
    }
  }
}
```

Run from the example directory with Herd PHP on PATH (`export PATH="/d/laravel13.x/bin:$PATH"`).
