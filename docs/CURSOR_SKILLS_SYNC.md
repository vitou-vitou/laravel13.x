# Cursor skills sync — same account, any PC

**Purpose:** One checklist so Spec-Kit, OpenSpec, Superpowers, and study-packet workflows work on every machine.

---

## Personal skills manifest (`~/.cursor/skills/`)

| Skill folder | Role | Triggers |
|--------------|------|----------|
| `spec-kit/` | Spec-Kit SDD + git extensions | `/speckit.*`, greenfield MVP, specify CLI |
| `openspec/` | OpenSpec OPSX workflow | `/opsx:*`, post-MVP changes |
| `superpowers/` | TDD, debugging, plans, review | implement, bugfix, verify, brainstorm |
| `spec-kit-openspec-superpowers/` | Triad router (+ optional Caveman voice) | spec-driven, triad setup |
| `caveman-spec-triad/` | Caveman voice + triad stack preset (no auto SDD) | `/Caveman spec kit Openspec Superpower`, `Use caveman spec kit openspec superpower:` |
| `system-study-packet/` | Repo-specific 8-principle + decomposition MD | study packet, system map, learn codebase |
| `8-principle-study/` | General topic study packets (docx/PDF/HTML/MD) | help me learn, flashcards, quiz, revise |
| `laravel-specialist/` | Laravel 10+ models, APIs, queues, Livewire, tests | Laravel, Eloquent, Sanctum, Pest |
| `domain-publish-pipeline/` | Domain research → study → calendar → 3am schedule → report | topic to daily post, Angkor, TikTok schedule, content factory |
| `impeccable/` | Frontend UI design, polish, audit | design, polish, `$impeccable` |
| `laravel-ui-phase/` | Post-MVP UI for `examples/*` (`AI pick my UI`) | AI pick my UI, Breeze gray, polish storefront |
| `design-taste-frontend/` | Anti-slop (claude-skills pack in `.agents/skills/`) | landing redesign, anti-slop catalog |

**Do not** write to `~/.cursor/skills-cursor/` (Cursor built-ins only).

---

## Two skill paths (do not confuse)

| Path | What lives here | Synced by Cursor account? |
|------|-----------------|---------------------------|
| `~/.cursor/skills/` | Personal triad + study-packet skills (this repo’s `.cursor/skills/` mirror) | Partially — copy from repo or Settings Sync; see checklist below |
| `~/.agents/skills/` | **claude-skills** bulk pack (~100+ community skills) | **No** — install manually on **each PC** |

These are separate directories. Cursor does **not** auto-sync `~/.agents/skills/` when you sign in.

### claude-skills bulk install (every new PC)

```bash
npx skills add alirezarezvani/claude-skills -g -y
```

- `-g` → global install into `~/.agents/skills/`
- `-y` → non-interactive (no prompts)

**Verify** (expect ~100+ folders):

```bash
ls ~/.agents/skills | wc -l
```

Windows (Git Bash): same paths under `$HOME/.agents/skills/`.

**Updates:** re-run the same `npx` command on each machine when the upstream pack changes.

---

### Sub-reference files (inside each skill)

| Skill | `reference/` contents |
|-------|----------------------|
| `spec-kit/` | git-commit, git-feature, git-initialize, git-remote, git-validate, laravel13-x-policy |
| `openspec/` | apply-change, archive-change, explore, propose |
| `superpowers/` | 14 sub-skills (TDD, brainstorming, debugging, plans, etc.) |
| `8-principle-study/` | packet-structure + `scripts/` (docx builder) |
| `laravel-specialist/` | eloquent, routing, queues, livewire, testing |
| `impeccable/` | craft, audit, live, … + `scripts/` |

---

## Local mirror (this repo)

Copy of personal skills for backup and new machines:

```
.cursor/skills/
├── spec-kit/
├── openspec/
├── superpowers/
├── spec-kit-openspec-superpowers/
├── caveman-spec-triad/
├── system-study-packet/
├── 8-principle-study/
├── laravel-specialist/
├── domain-publish-pipeline/
├── laravel-ui-phase/
└── impeccable/
```

After clone on a new PC:

```bash
cd /path/to/laravel13.x
mkdir -p ~/.cursor/skills
cp -r .cursor/skills/* ~/.cursor/skills/
```

Windows (Git Bash):

```bash
mkdir -p "$USERPROFILE/.cursor/skills"
cp -r .cursor/skills/* "$USERPROFILE/.cursor/skills/"
```

---

## Superpowers plugin (optional extra)

1. Cursor → **Plugins** → install **Superpowers** (obra/superpowers).
2. Same Cursor account + **Settings Sync** on each PC.
3. Personal skill `superpowers/` syncs even without the plugin.

Deprecated slash commands → use **superpowers** reference:

| Old command | Use |
|-------------|-----|
| `/brainstorm` | superpowers → brainstorming |
| `/write-plan` | superpowers → writing-plans |
| `/execute-plan` | superpowers → executing-plans |

---

## Caveman plugin (optional — token compression)

Caveman is a **Cursor plugin**, not a repo-mirrored personal skill. On this PC it lives at `~/.cursor/plugins/cache/caveman/` (not in `~/.cursor/skills/` or `.cursor/skills/`).

### Install (every new PC)

**Preferred — Cursor plugin marketplace:**

1. Cursor → **Plugins** → install **caveman** (`JuliusBrussee/caveman`).
2. Same Cursor account + **Settings Sync** (plugin list may sync; re-install manually if missing).

**Alternative — skills CLI** (copies skill folders into `~/.agents/skills/`):

```bash
npx skills add JuliusBrussee/caveman -a cursor
```

**All agents on one machine** (Git Bash or PowerShell):

```bash
curl -fsSL https://raw.githubusercontent.com/JuliusBrussee/caveman/main/install.sh | bash
# Windows PowerShell: irm .../install.ps1 | iex
```

### Bundled skills (via plugin or `npx`)

| Skill | Triggers |
|-------|----------|
| `caveman/` | `/caveman`, "talk like caveman", "less tokens" |
| `caveman-commit/` | commit message, `/commit` |
| `caveman-review/` | PR review, `/review` |
| `caveman-compress/` | compress memory files |
| `caveman-help/` | command reference |
| `caveman-stats/` | session token savings |
| `cavecrew/` | compressed subagents (investigator/builder/reviewer) |

**Verify** (plugin cache present):

```bash
ls ~/.cursor/plugins/cache/caveman/caveman/*/skills/caveman/SKILL.md
```

Or after `npx` install: `ls ~/.agents/skills/caveman/SKILL.md`.

**Updates:** re-install plugin or re-run `npx skills add JuliusBrussee/caveman -a cursor`.

---

## CLI tools (install per machine)

| Tool | Install | Verify |
|------|---------|--------|
| **Spec-Kit** | `uv tool install specify-cli --from git+https://github.com/github/spec-kit.git` | `specify check` |
| **OpenSpec** | `npm install -g @fission-ai/openspec@latest` | `openspec --version` |
| **PHP (Herd)** | Laravel Herd | `php artisan test` in each example |

---

## Workflow quick reference

```
GREENFIELD (examples/* MVP)
  spec-kit: /speckit.constitution → specify → plan → tasks → implement
  superpowers: TDD + verification on every task
  caveman (optional): terse voice — does not replace SDD or Superpowers
  NO openspec init at greenfield

POST-MVP (laravel13.x repo)
  openspec: /opsx:new → /opsx:continue or /opsx:ff → /opsx:apply → /opsx:archive
  superpowers: same TDD loop during apply
  caveman (optional): same voice layer as greenfield

UI PHASE (examples/* — after tests green)
  laravel-ui-phase: read docs/GITHUB_UI_RESOURCE_INDEX.md → write docs/DESIGN.md
  impeccable + design-taste-frontend: audit/polish Blade (same routes)
  superpowers: verification-before-completion (php artisan test)
  User says: "AI pick my UI" or "polish all pages"

SESSION RESUME
  User says "continue" → read docs/SESSION_STATE.md
```

---

## Cursor account sync checklist

- [ ] Signed into same Cursor account
- [ ] Settings Sync enabled (if available in your Cursor version)
- [ ] `~/.cursor/skills/` has spec-kit, openspec, superpowers, 8-principle-study, laravel-specialist
- [ ] `~/.agents/skills/` installed via `npx skills add alirezarezvani/claude-skills -g -y` (each PC)
- [ ] Optional: Superpowers plugin installed
- [ ] Optional: Caveman plugin installed (or `npx skills add JuliusBrussee/caveman -a cursor`)
- [ ] `specify` and `openspec` CLIs installed
- [ ] Repo cloned; `docs/SESSION_STATE.md` present

---

## Verify in Agent chat

```text
Use spec-kit-openspec-superpowers: verify my triad setup for laravel13.x on this machine.
```

```text
Use spec-kit: validate the current feature branch.
```

```text
Use openspec: list active changes.
```

```text
Use superpowers: implement the next task with TDD.
```

```text
Use laravel-ui-phase: AI pick my UI for examples/marketplace-v2 — all pages, keep tests green.
```

```text
Use impeccable: audit checkout UX in examples/kindly-e-commerce-1122.
```

```text
Use 8-principle-study: build a Markdown study packet on JWT auth for a student.
```

```text
Use laravel-specialist: review Eloquent relationships in examples/jwt.
```

```text
Use caveman: talk like caveman for the rest of this session.
```

```text
/Caveman spec kit Openspec Superpower
```

(or `Use caveman spec kit openspec superpower:` — loads **caveman-spec-triad**: caveman voice + triad manuals; does not auto-run SDD)

---

*Update this file when adding new personal skills to `.cursor/skills/`.*
