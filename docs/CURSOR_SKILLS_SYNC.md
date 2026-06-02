# Cursor skills sync — same account, any PC

**Purpose:** One checklist so Spec-Kit, OpenSpec, Superpowers, and study-packet workflows work on every machine.

---

## Personal skills manifest (`~/.cursor/skills/`)

| Skill folder | Role | Triggers |
|--------------|------|----------|
| `spec-kit-openspec-superpowers/` | Triad guide + laravel13.x policy | spec-driven, speckit, opsx, superpowers |
| `system-study-packet/` | 8-principle + decomposition MD | study packet, system map |
| `openspec-apply-change/` | Implement OpenSpec change | `/opsx:apply`, continue implementation |
| `openspec-archive-change/` | Archive completed change | `/opsx:archive` |
| `openspec-explore/` | Explore before committing | `/opsx:explore` |
| `openspec-propose/` | One-shot proposal | `/opsx:ff`, new change |
| `speckit-git-commit/` | Commit after speckit | post `/speckit.*` |
| `speckit-git-feature/` | Feature branch naming | new feature branch |
| `speckit-git-initialize/` | Init repo | new repo |
| `speckit-git-remote/` | Remote detection | GitHub setup |
| `speckit-git-validate/` | Branch name validation | branch check |

**Do not** write to `~/.cursor/skills-cursor/` (Cursor built-ins only).

---

## Git mirror (this repo)

Copy of personal skills for backup and new machines:

```
.cursor/skills/
├── spec-kit-openspec-superpowers/
├── system-study-packet/
├── openspec-*/
└── speckit-git-*/
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

## Superpowers (plugin — not in `~/.cursor/skills/`)

1. Cursor → **Plugins** → install **Superpowers** (obra/superpowers).
2. Same Cursor account + **Settings Sync** on each PC.
3. Skills auto-load: `brainstorming`, `test-driven-development`, `verification-before-completion`, etc.

Deprecated slash commands → use skills instead:

| Old command | Use skill |
|-------------|-----------|
| `/brainstorm` | `brainstorming` |
| `/write-plan` | `writing-plans` |
| `/execute-plan` | `executing-plans` |

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
  Spec-Kit: /speckit.constitution → specify → plan → tasks → implement
  Superpowers: TDD + verification on every task
  NO openspec init at greenfield

POST-MVP (laravel13.x repo)
  OpenSpec: /opsx:new → /opsx:continue or /opsx:ff → /opsx:apply → /opsx:archive
  Superpowers: same TDD loop during apply

SESSION RESUME
  User says "continue" → read docs/SESSION_STATE.md
```

---

## Cursor account sync checklist

- [ ] Signed into same Cursor account
- [ ] Settings Sync enabled (if available in your Cursor version)
- [ ] Superpowers plugin installed
- [ ] `~/.cursor/skills/` populated (from sync or `cp` from repo)
- [ ] `specify` and `openspec` CLIs installed
- [ ] Repo cloned; `docs/SESSION_STATE.md` present
- [ ] Optional: study packets under `examples/*/docs/study-packets/`

---

## Verify in Agent chat

```text
Use spec-kit-openspec-superpowers: verify my triad setup for laravel13.x on this machine.
```

```text
Use system-study-packet: list what study docs exist for kindly-e-commerce.
```

---

*Update this file when adding new personal skills to `.cursor/skills/`.*
