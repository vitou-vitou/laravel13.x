# Multi-Project Workflow — How Pros Juggle Many Repos

How professional devs maintain concurrent projects without losing their minds.

---

## 1. Workspace Isolation

- **git worktree** — one repo, many branches, many folders. No stash dance.
- **devcontainer / docker** — each project owns its deps; no version wars.
- **direnv / mise** — auto-swap node/php/python versions per folder.
- **tmux / wezterm sessions** — one session per project, restore on boot.

## 2. Fast Context Switching

- **CLAUDE.md per repo** — AI remembers project rules.
- **README "quickstart"** — past-you writing for future-you. Boot command, env, test command.
- **Makefile / justfile** — `make dev`, `make test` — same word every project.
- **`.env.example` committed**, real `.env` ignored.

## 3. Task Tracker, Not Brain

- **One inbox** (Linear / ClickUp / GitHub Project) — not 5 sticky notes.
- **Daily 5-minute triage** — what moves today? Rest sleeps.
- **"Now / Next / Later" columns** — never more than 3 items in Now.

## 4. Async Beats Sync

- **Small PRs** — review fast, merge fast, clear mind.
- **Draft PR early** — CI runs while you think.
- **Background long jobs** — tests/builds/deploys don't block you.

## 5. Conventions Everywhere

- Same folder layout, same lint config, same commit style.
- Brain doesn't re-learn each repo.

## 6. Typical Pro Tool Stack

| Job        | Tool                       |
| ---------- | -------------------------- |
| Editor     | VSCode / Neovim + LSP      |
| AI pair    | Claude Code                |
| Term mux   | tmux / wezterm             |
| Git UI     | lazygit / gh CLI           |
| Task       | Linear / Obsidian          |
| Time block | Calendar + Pomodoro        |

## 7. Hard Rules

- **WIP limit** — max 2-3 active branches. More = dropped balls.
- **End-of-day brain dump** — write next-step in TODO, close laptop.
- **Friday cleanup** — close stale PRs, prune branches, update docs.

---

## The Big Secret

Pros aren't smarter. Pros have systems so the brain doesn't hold state.
**The system holds state. The brain does work.**
