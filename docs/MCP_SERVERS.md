# Cursor MCP servers (laravel13.x)

Aligned stack for this monorepo: **docs + UI + browser QA + Laravel package docs**.

| Config | Path |
|--------|------|
| **Live (this machine)** | `~/.cursor/mcp.json` — keys configured; never commit |
| **Template (repo)** | [`cursor-mcp.example.json`](cursor-mcp.example.json) — placeholders only |

See also: [`cursor-notion-plugin.md`](cursor-notion-plugin.md) · [`BROWSER_VIDEO_MCP.md`](BROWSER_VIDEO_MCP.md) · [`GITHUB_UI_RESOURCE_INDEX.md`](GITHUB_UI_RESOURCE_INDEX.md) · [`CURSOR_SKILLS_SYNC.md`](CURSOR_SKILLS_SYNC.md)

---

## Active stack (2026-06-14)

**Global** `~/.cursor/mcp.json` servers + **Cursor plugins** (Notion). Reload MCP after edits.

| MCP name | Cursor id | Install | Auth |
|----------|-----------|---------|------|
| **notion** | `plugin-notion-workspace-notion` | **Cursor plugin** (not `mcp.json`) | OAuth ✓ |
| **playwright** | `user-playwright` | `~/.cursor/mcp.json` | — |
| **browsermcp** | `user-browsermcp` | `~/.cursor/mcp.json` | Chrome extension |
| **@21st-dev/magic** | `user-@21st-dev/magic` | `~/.cursor/mcp.json` | `API_KEY` ✓ |
| **context7** | `user-context7` | `~/.cursor/mcp.json` | `CONTEXT7_API_KEY` ✓ |
| **aikido** | — | `~/.cursor/mcp.json` | `AIKIDO_API_KEY` ✓ |
| **prompts.chat** | `user-prompts.chat` | `~/.cursor/mcp.json` | `PROMPTS_API_KEY` ✓ |
| **superhuman-mail** | `user-superhuman-mail` | `~/.cursor/mcp.json` | OAuth |
| **sonarqube** | `user-sonarqube` | `~/.cursor/mcp.json` + Docker | `SONARQUBE_TOKEN` + `SONARQUBE_ORG` or `SONARQUBE_URL` |
| **cursor-ide-browser** | built-in | Cursor | — |

GitHub / docs: [Notion MCP](https://developers.notion.com/docs/mcp) · [cursor-notion-plugin](https://github.com/makenotion/cursor-notion-plugin) · [playwright-mcp](https://github.com/microsoft/playwright-mcp) · [BrowserMCP/mcp](https://github.com/BrowserMCP/mcp) · [21st-dev/magic-mcp](https://github.com/21st-dev/magic-mcp) · [upstash/context7](https://github.com/upstash/context7) · [SonarSource/sonarqube-mcp-server](https://github.com/SonarSource/sonarqube-mcp-server)

**Notion endpoint (plugin-managed):** `https://mcp.notion.com/mcp` — do **not** duplicate in `mcp.json`.

---

## Agent routing (pick one per task)

```
laravel13.x work
├── Code + specs (source of truth)        → git: docs/SESSION_STATE.md, examples/*/docs/NEXT_SESSION.md, openspec/
├── Notion tasks / notes (when you ask)   → notion plugin (search, create-pages, database rows)
├── Laravel / Livewire / Stripe facts     → context7
├── New UI components                     → @21st-dev/magic
├── Visual QA @ http://<slug>.test        → playwright
├── Logged-in Chrome                      → browsermcp
├── Behavior / regressions                → php artisan test (always)
└── Security on changed code              → aikido_full_scan (or sonarqube MCP for SonarCloud/Server issues)
```

**Git wins for handoff.** Use Notion for personal task boards, meeting notes, or exporting a session summary — not as a replacement for `SESSION_STATE.md` / `NEXT_SESSION.md`.

Do **not** run playwright + browsermcp + cursor-ide-browser on the same flow unless the user asks.

---

## Notion (Cursor plugin)

Installed via **Cursor → Plugins → Notion** ([makenotion/cursor-notion-plugin](https://github.com/makenotion/cursor-notion-plugin)). Bundles hosted MCP + skills. **Not** added to `cursor-mcp.example.json` — OAuth is plugin-managed.

### MCP tools (14)

| Tool | Use |
|------|-----|
| `notion-search` | Workspace search |
| `notion-fetch` | Read page / database |
| `notion-create-pages` | New pages |
| `notion-update-page` | Edit page |
| `notion-move-pages` / `notion-duplicate-page` | Organize |
| `notion-create-database` / `notion-update-data-source` | Databases |
| `notion-create-view` / `notion-update-view` | Views |
| `notion-create-comment` / `notion-get-comments` | Comments |
| `notion-get-users` / `notion-get-teams` | People |

First use: complete OAuth when prompted (or agent calls `mcp_auth` on `plugin-notion-workspace-notion`).

### Plugin skills (pair with repo workflow)

| Skill | laravel13.x use |
|-------|-----------------|
| `knowledge-capture` | Save session decisions to Notion wiki (git docs stay canonical) |
| `spec-to-implementation` | OpenSpec / PRD page → Notion task breakdown |
| `tasks-plan` / `tasks-build` | Track a Notion task URL through implementation |
| `tasks-explain-diff` | Post-merge doc explaining what changed |
| `create-task` / `database-query` | Personal task board |
| `search` / `find` | Find existing Notion specs before coding |

### Example prompts

```text
Search my Notion for marketplace-v2 roadmap

Capture this session to Notion: marketplace-v2 Phase 5 ideas (link git docs/SESSION_STATE.md topics only)

Create Notion tasks from openspec/changes/money-and-trust-v1/ spec

Add a task: verify marketplace-v2 screenshots on iPhone 14 — due Friday
```

---

## 21st.dev Magic

| Tool | Use |
|------|-----|
| `21st_magic_component_builder` | Generate UI from natural language |
| `21st_magic_component_refiner` | Polish existing component |
| `21st_magic_component_inspiration` | Browse 21st.dev patterns |
| `logo_search` | Brand logos via SVGL |

**Chat:** `/ui create a compact mobile product card…`  
**Align with:** `examples/*/docs/DESIGN.md`, `GITHUB_UI_RESOURCE_INDEX.md`, skills `impeccable` + `design-taste-frontend`

---

## Context7

| Tool | Use |
|------|-----|
| `resolve-library-id` | Map “Laravel” → `/laravel/docs` (etc.) |
| `query-docs` | Version-specific answers |

**Chat:** `use context7 — Laravel 13 form request validation for nested cart lines`  
**Prefer over** web search for package API syntax, migrations, Stripe SDK calls.

---

## Playwright vs Browser MCP

| | **playwright** | **browsermcp** |
|--|----------------|----------------|
| Browser | Fresh Chromium | Your Chrome profile |
| Login state | No | Yes (extension) |
| Video | `browser_start_video` + `--caps=devtools` | No |
| Screenshots in repo | Save to home → `cp` to `examples/*/docs/screenshots/` | `browser_screenshot` |
| Windows | `npx` | `cmd /c npx` + `CHROME_PATH` |

**Prerequisite:** `npx playwright install chromium` (once per machine).

**marketplace-v2 screenshots** (Playwright viewports):

| Device | Size | File |
|--------|------|------|
| iPhone 14 | 390×844 | `examples/marketplace-v2/docs/screenshots/catalog-iphone-14.png` |
| Pixel 7 | 412×915 | `examples/marketplace-v2/docs/screenshots/catalog-pixel-7.png` |
| iPad | 810×1080 | `examples/marketplace-v2/docs/screenshots/catalog-tablet-ipad.png` |

Playwright MCP cannot write outside `C:\Users\vitou` — copy into the repo after capture.

---

## SonarQube MCP (code quality + security)

**Official repo:** [SonarSource/sonarqube-mcp-server](https://github.com/SonarSource/sonarqube-mcp-server) (picked over community forks — maintained by SonarSource, Antigravity MCP Store listing, Docker image `mcp/sonarqube`).

**Search used:** `gh search repos "sonarqube mcp server"` → top hit `SonarSource/sonarqube-mcp-server`.

### Install (Cursor + Docker)

1. **Start Docker Desktop** (required — daemon was not running on this machine during install).
2. Prefill config: [SonarQube MCP config generator](https://mcp.sonarqube.com/config-generator.html) — or use the block already added to `~/.cursor/mcp.json` from [`cursor-mcp.example.json`](cursor-mcp.example.json).
3. Replace placeholders in `~/.cursor/mcp.json`:
   - **SonarQube Cloud:** `SONARQUBE_TOKEN` + `SONARQUBE_ORG` (org key from SonarCloud)
   - **SonarQube Cloud US:** also set `"SONARQUBE_URL": "https://sonarqube.us"` in `env` and add `"-e", "SONARQUBE_URL"` to `args`
   - **Self-hosted Server:** swap `SONARQUBE_ORG` for `SONARQUBE_URL` (see example JSON in repo template)
4. Pull image once: `docker pull mcp/sonarqube`
5. Cursor → **Settings → MCP** → reload **sonarqube**

**Token:** SonarCloud → My Account → Security → Generate Tokens (User token, not project token).

**Cursor one-click (Cloud):** [Install deeplink](https://cursor.com/en-US/install-mcp?name=sonarqube&config=eyJlbnYiOnsiU09OQVJRVUJFX1RPS0VOIjoiWU9VUl9UT0tFTiIsIlNPTkFSUVVCRV9PUkciOiJZT1VSX1NPTkFSUVVCRV9PUkcifSwiY29tbWFuZCI6ImRvY2tlciBydW4gLS1pbml0IC0tcHVsbD1hbHdheXMgLWkgLS1ybSAtZSBTT05BUlFVQkVfVE9LRU4gLWUgU09OQVJRVUJFX09SRyBtY3Avc29uYXJxdWJlIn0%3D)

Example prompts once connected:

```text
List open blocker issues for project laravel13-x on SonarQube
Analyze this PHP snippet for security hotspots before I commit
What's failing our quality gate on the main branch?
```

---

## New machine setup

### `~/.cursor/mcp.json` (manual servers)

1. Copy [`cursor-mcp.example.json`](cursor-mcp.example.json) → `~/.cursor/mcp.json`
2. Fill keys: [21st Magic Console](https://21st.dev/magic/console), [Context7 dashboard](https://context7.com/dashboard), Aikido, prompts.chat, SonarQube token/org
3. `npx playwright install chromium`
4. Install [Browser MCP extension](https://browsermcp.io/) → **Connect**
5. Reload all MCP servers in Cursor Settings

### Notion (separate — Cursor plugin)

1. Cursor → **Plugins** → install **Notion**
2. Enable **notion** MCP in Settings → MCP
3. Complete OAuth on first use
4. Optional: `tasks-setup` skill to connect a task database

Do **not** paste Notion into `mcp.json` if the plugin is already enabled (duplicate server).

Or partial install:

```bash
npx @21st-dev/cli@latest install cursor --api-key <key>
npx ctx7 setup --cursor
```

---

## Example prompts (marketplace-v2)

```text
use context7 — Livewire 4 wire:model on cart quantity inputs

/ui mobile sticky cart bar matching DESIGN.md tokens

Playwright MCP: iPhone 14 + Pixel 7 + tablet viewports, catalog screenshots → docs/screenshots/

Browser MCP: open http://marketplace-v2.test/vendor as logged-in vendor, snapshot dashboard
```

---

## Per-example MCP (Laravel Boost)

`examples/*/cursor/mcp.json` **merges** with global — e.g. Boost only:

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

Run from example dir: `export PATH="/d/laravel13.x/bin:$PATH"`.

---

## Troubleshooting (Windows + Herd)

| Issue | Fix |
|-------|-----|
| browsermcp tools missing | Chrome extension **Connect**; `cmd /c npx` config |
| browsermcp vs Cursor conflict | One client owns extension — disable duplicate browser MCP |
| playwright browser not found | `npx playwright install chromium` |
| 21st `/ui` fails | Check `API_KEY`; reload `@21st-dev/magic` |
| context7 empty / 401 | Check `CONTEXT7_API_KEY` header; reload **context7** |
| notion tools missing | Cursor plugin installed; complete OAuth; reload **notion** |
| notion duplicate server | Remove manual `mcp.notion.com` entry from `mcp.json` if plugin active |
| sonarqube MCP red / no tools | Start **Docker Desktop**; `docker pull mcp/sonarqube`; set real `SONARQUBE_TOKEN` (not placeholder) |
| Herd 404 | `herd link <slug>`; use `APP_URL` from `.env` |
| Screenshot not in repo | Copy from `%USERPROFILE%\catalog-*.png` into `docs/screenshots/` |
