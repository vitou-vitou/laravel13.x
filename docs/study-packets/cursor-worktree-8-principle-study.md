# Cursor Git Worktrees + Laravel

**A Structured Study Packet**  
Built with an 8-principle learning method

---

## How to use this packet

This packet teaches **git worktrees in Cursor** on Windows with **Laravel / Herd**: keep local feature WIP in one folder, spin up a separate folder for production hotfixes, and bootstrap `composer`, `npm`, and `.env` per worktree without branch-hopping or stash chaos.

Paths reflect this machine's layout (`D:/laravel13.x`, Cursor worktrees under `~/.cursor/worktrees/laravel13.x/`). Adjust `LARAVEL_MAIN_CLONE` if your main clone lives elsewhere.

**Audience:** Laravel developer on Windows + Herd + Git Bash — student / general learner level.

**Bootstrap script:** `./bin/bootstrap-worktree [example-slug]` — run once per new worktree.

**Step 1 — Understanding** (Principles 1–4): build a correct mental model.  
**Step 2 — Automaticity** (Principles 5–8): quizzes, spacing, mixing, and overlearning.

### The 8 principles

| # | Principle | What you do |
|---|-----------|-------------|
| 1 | Map of the system | See how parts connect |
| 2 | Clear explanations | Learn core ideas in plain language |
| 3 | Different media | Same ideas as summary, diagram, analogy, table |
| 4 | Short lessons | Bite-sized micro-lessons |
| 5 | Test yourself | Quiz + flashcards + answer key |
| 6 | Wait to review | Spaced repetition schedule |
| 7 | Mix it up | Interleaved quiz |
| 8 | Don't stop | Overlearning plan |

### Table of contents

- [Step 1 — Understanding](#step-1--understanding)
  - [Principle 1 — Map of the system](#principle-1--map-of-the-system)
  - [Principle 2 — Clear explanations](#principle-2--clear-explanations)
  - [Principle 3 — Different media](#principle-3--different-media)
  - [Principle 4 — Short lessons](#principle-4--short-lessons)
- [Step 2 — Automaticity](#step-2--automaticity)
  - [Principle 5 — Test yourself](#principle-5--test-yourself)
  - [Principle 6 — Wait to review](#principle-6--wait-to-review)
  - [Principle 7 — Mix it up](#principle-7--mix-it-up)
  - [Principle 8 — Don't stop](#principle-8--dont-stop)
- [Appendix — Glossary](#appendix--glossary)
- [Quick command cheat sheet](#quick-command-cheat-sheet)

---

# Step 1 — Understanding

Your goal: a simple, accurate picture of one git repo with multiple folders, each bound to a Cursor chat and branch, with Laravel deps installed per folder.

---

## Principle 1 — Map of the system

### Ecosystem table

| Part | What it is | This machine |
|------|------------|--------------|
| **Main clone** | Primary checkout, usually `main` | `D:/laravel13.x` |
| **Cursor worktree** | Extra folder, own branch, shared `.git` | `C:/Users/PGI/.cursor/worktrees/laravel13.x/<id>/` |
| **Git common dir** | One repo brain — history, remotes, branches | `D:/laravel13.x/.git` |
| **Per-tree deps** | `vendor/`, `node_modules/`, `.env` — not shared | Each folder runs its own install |
| **Cursor chat** | Bound to one workspace root | One chat = one worktree path |
| **Herd** | Serves PHP per linked path | `herd link` per app per folder |
| **bootstrap-worktree** | Repo script — composer/npm/.env | `./bin/bootstrap-worktree <slug>` |

### Lifecycle flow

| Stage | Action | Tool |
|-------|--------|------|
| 1 | Need isolated work (feature or hotfix) | Cursor **New Agent → Worktree** or `git worktree add` |
| 2 | Bootstrap deps once | `./bin/bootstrap-worktree [slug]` |
| 3 | Code, test, commit, push | Normal git + `php artisan test` |
| 4 | Merge PR to `main` | GitHub / `gh pr merge` |
| 5 | Sync other worktrees | `git merge origin/main` in feature folder |
| 6 | Remove hotfix tree | `git worktree remove <path>` |

### Recommended role map

| Folder | Branch | When to use |
|--------|--------|-------------|
| `D:/laravel13.x` | `main` | Reference, fetch, hotfix base |
| `…/pbmq` (example) | `feature/*` | Daily local WIP |
| `…/hotfix-*` (on demand) | `hotfix/*` from `origin/main` | Production emergency only |

**Map takeaway:** One git repo, many folders. Each folder = one branch + one Cursor window. Local code stays local because you never `git checkout` away from it.

---

## Principle 2 — Clear explanations

### What is a git worktree?

A **worktree** is a second (or third) working directory attached to the same repository. All worktrees share commit history and remotes, but each has its own checked-out branch and files on disk.

### Why not `git stash` + `git checkout`?

Stash works for minutes. For hours or days of WIP, stash gets lost, conflicts on return, and you lose context. Worktrees keep WIP **on disk, on its branch, in its folder**.

### How does Cursor create worktrees?

When you start a **new Agent chat with Worktree** (or Cursor auto-creates one — *"Created worktree for …"*):

1. Cursor runs `git worktree add` under `~/.cursor/worktrees/<repo>/`
2. Creates a branch (often `feature/<hash>`)
3. Opens that path as the workspace root for that chat
4. Agent tools run **only** in that path

Your main clone at `D:/laravel13.x` is untouched.

### What is shared vs separate?

| Shared | Separate per worktree |
|--------|----------------------|
| Git history, branches, remotes | Working files on that branch |
| `git fetch`, `git log` | Uncommitted changes |
| Composer/npm **cache** | `vendor/`, `node_modules/` |
| — | `.env` |
| — | Running dev servers |

### How does Laravel fit in?

Each worktree needs a one-time bootstrap:

```bash
export PATH="/d/laravel13.x/bin:$PATH"
./bin/bootstrap-worktree kindly-e-commerce-1122
```

The script runs `composer install`, `npm ci`, copies `.env` from main clone (or `.env.example`), optionally runs `verify-example`.

### Hotfix vs feature

| Type | Branch from | Cursor action |
|------|-------------|---------------|
| **Feature** | `main` or dev line | New Agent → Worktree |
| **Hotfix** | `origin/main` | New Agent → Worktree, **base = main** |
| **Never** | — | Fix prod inside feature worktree |

After hotfix merges, run `git merge origin/main` in feature worktrees.

### Am I in a worktree?

```bash
git worktree list
git branch --show-current
pwd
```

If `git rev-parse --git-dir` ≠ `git rev-parse --git-common-dir`, you're in a linked worktree.

**Explanation takeaway:** Worktrees = parallel folders, one repo. Cursor binds each chat to one folder. Laravel deps are per-folder. Hotfixes get their own tree from `main`.

**Common misconception:** Worktrees share `vendor/`. They don't — only git objects are shared.

---

## Principle 3 — Different media

### One-line summary

> Keep feature WIP in one Cursor worktree; spin a second from `main` for hotfixes; run `./bin/bootstrap-worktree` once per folder; merge back when done.

### Diagram — three windows, one repo

```
                    ┌─────────────────────────┐
                    │   D:/laravel13.x/.git   │
                    │   (single repo brain)   │
                    └───────────┬─────────────┘
          ┌─────────────────────┼─────────────────────┐
          ▼                     ▼                     ▼
   D:/laravel13.x        …/pbmq (feature)     …/hotfix-jul4
   branch: main          feature/*              hotfix/*
   vendor ✓               vendor ✓               vendor ✓
   .env ✓                 .env ✓                 .env ✓
```

### Analogy

One **library card** (git repo), three **study desks** (worktrees). Same catalog, different open pages. Walk to another desk — don't throw away desk A's notes.

### Comparison table

| Approach | WIP safe? | Swap speed | Laravel setup | Cursor-native? |
|----------|-----------|------------|---------------|----------------|
| **Worktree** | Yes | Open other window | `./bin/bootstrap-worktree` | Yes |
| **Stash + checkout** | Risky | Slow | Re-confuse `.env` | No |
| **Second full clone** | Yes | Medium | Full duplicate | Manual |
| **Commit WIP early** | OK if clean | Medium | Same folder | No isolation |

**Media takeaway:** Worktrees = library with multiple desks — best fit for Cursor + Laravel.

---

## Principle 4 — Short lessons

### Lesson 1 — Check landscape

```bash
cd D:/laravel13.x
git worktree list
git fetch origin
```

### Lesson 2 — Start feature work (Cursor)

1. New Agent → **Worktree**
2. Agent lands in `~/.cursor/worktrees/laravel13.x/<id>/`
3. Bootstrap once:

```bash
export PATH="/d/laravel13.x/bin:$PATH"
./bin/bootstrap-worktree kindly-e-commerce-1122
```

4. Code normally — commits stay on feature branch

### Lesson 3 — Production hotfix (Cursor)

1. **Do not** use feature chat/folder
2. New Agent → Worktree, base **`origin/main`**
3. Rename branch to `hotfix/short-description` if needed
4. Bootstrap + fix + test + push + merge

### Lesson 4 — Sync fix into feature worktree

```bash
git fetch origin
git merge origin/main
```

Uncommitted WIP remains; you add upstream commits only.

### Lesson 5 — Manual worktree

```bash
cd D:/laravel13.x
git fetch origin
git worktree add ../laravel13-hotfix -b hotfix/payment origin/main
cd ../laravel13-hotfix
./bin/bootstrap-worktree <slug>
```

Open folder in Cursor: **File → Open Folder**.

### Lesson 6 — Clean up

```bash
cd D:/laravel13.x
git worktree remove ../laravel13-hotfix
git branch -d hotfix/payment
git worktree prune
```

**Short-lessons takeaway:** Feature = default worktree chat. Hotfix = new tree from `main`. Sync = merge `origin/main`. Cleanup = `worktree remove`.

---

# Step 2 — Automaticity

Understanding fades. These practices make the workflow automatic.

---

## Principle 5 — Test yourself

### Quiz

1. What directory holds shared git history for all worktrees?
2. Why doesn't feature WIP disappear when you open a hotfix worktree?
3. Name three things **not** shared between worktrees.
4. What branch should a production hotfix start from?
5. Where does Cursor put worktrees on this machine?
6. What command lists all worktrees?
7. After hotfix merges to `main`, what do you run in the feature worktree?
8. Why run `composer install` in each new worktree?
9. Should two worktrees run `npm run dev` on the same Herd slug at once?
10. How do you detect a linked worktree vs main clone?

### Answer key

1. `D:/laravel13.x/.git` (git common dir)
2. Separate folder and branch — no checkout swap
3. Any three: `vendor/`, `node_modules/`, `.env`, uncommitted files, dev servers
4. `origin/main` (or production release branch)
5. `C:/Users/PGI/.cursor/worktrees/laravel13.x/<id>/`
6. `git worktree list`
7. `git fetch origin` then `git merge origin/main`
8. `vendor/` is gitignored local state
9. No — slug/port conflict
10. `git rev-parse --git-dir` ≠ `git rev-parse --git-common-dir`

### Flashcards

| Front | Back |
|-------|------|
| Worktree | Extra working dir, same repo, own branch |
| Cursor worktree path | `~/.cursor/worktrees/laravel13.x/<id>/` |
| Main clone | `D:/laravel13.x` on `main` |
| Hotfix branch | `hotfix/*` from `origin/main` |
| Feature branch | `feature/*` |
| List worktrees | `git worktree list` |
| Remove worktree | `git worktree remove <path>` |
| Bootstrap script | `./bin/bootstrap-worktree [slug]` |
| PHP on Git Bash | `export PATH="/d/laravel13.x/bin:$PATH"` |
| Verify example | `./bin/verify-example <slug>` |
| Sync hotfix → feature | `git merge origin/main` |
| Bad for long WIP | `git stash` + branch hopping |

---

## Principle 6 — Wait to review

| When | What to do | ☐ |
|------|------------|---|
| **Today** | `git worktree list` — name each path and branch | ☐ |
| **Day 1** | Open feature worktree; run tests | ☐ |
| **Day 3** | Quiz Q1–Q5 from memory | ☐ |
| **Day 7** | Dry-run hotfix: create tree, bootstrap, remove | ☐ |
| **Day 14** | Flashcards — 3 flawless rounds | ☐ |
| **Day 30** | Interleaved quiz cold, no notes | ☐ |

Spacing works because retrieval after fade strengthens memory more than one read-through.

---

## Principle 7 — Mix it up

1. Mid-feature in `pbmq`. PagerDuty fires. First action?
2. Same branch in two worktree paths. Problem?
3. Hotfix merged. Feature tree still on old base. Safe for WIP?
4. Copy `.env` from main clone to hotfix — good or bad?
5. Agent says "Created worktree for 7s". What happened?
6. `vendor/` missing in new worktree — expected?
7. Two feature worktrees — can both push different branches?
8. Delete worktree folder in Explorer without `git worktree remove`?
9. Herd 500 after new worktree — first checks?
10. Main clone vs Cursor worktree for daily work?

### Interleaved answer key

1. New worktree from `origin/main` — not feature folder
2. Yes — git forbids same branch in two paths
3. Safe; run `git merge origin/main` for the fix
4. Good locally; never commit `.env`
5. Cursor ran `git worktree add` + new branch
6. Yes — run `./bin/bootstrap-worktree`
7. Yes — isolated paths and branches
8. Orphan metadata — `git worktree prune`
9. `.env` locals, `verify-example`, APP_KEY, Redis host
10. Daily feature → worktree; main → fetch / reference / hotfix base

---

## Principle 8 — Don't stop

### Stages

| Stage | Sign | Action |
|-------|------|--------|
| First correct | Created one worktree | Repeat hotfix dry-run |
| Comfortable | Swap windows without notes | Real hotfix or merge-back drill |
| Automatic | Quiz 10/10 cold | Teach teammate; log slugs in NEXT_SESSION |

### Overlearning plan

- **Weekly:** `git worktree list` + `git worktree prune`
- **Per new tree:** `./bin/bootstrap-worktree <slug>`
- **Monthly:** Draw ecosystem diagram from memory
- **After hotfix:** One line in `docs/NEXT_SESSION.md` — tree path, branch
- **Rule:** Feature chat ≠ hotfix chat

**Final takeaway:** One repo, many folders. Bootstrap once per folder. Hotfix from `main`, merge back, remove tree. Practice until boring — that's when it sticks.

---

# Appendix — Glossary

| Term | Definition |
|------|------------|
| **Worktree** | Linked working directory sharing one git repository |
| **Main clone** | Primary checkout (`D:/laravel13.x`) |
| **Common dir** | Shared `.git` for all worktrees |
| **Cursor worktree** | Worktree under `.cursor/worktrees/` |
| **bootstrap-worktree** | `./bin/bootstrap-worktree` — composer/npm/.env |
| **Hotfix branch** | Short-lived branch from production (`hotfix/*`) |
| **Feature branch** | Longer-lived dev branch (`feature/*`) |
| **Herd link** | Maps folder to `http://<slug>.test` |
| **verify-example** | Health check for `examples/<slug>` |
| **worktree prune** | Cleans stale worktree records |

**Related docs:** `docs/multi-project-workflow.md`, `docs/WINDOWS_HERD_GITBASH.md`, `docs/EXAMPLE_DEV_LESSONS.md`

---

## Quick command cheat sheet

```bash
# Landscape
git worktree list
git fetch origin

# Bootstrap (run in any worktree root)
export PATH="/d/laravel13.x/bin:$PATH"
./bin/bootstrap-worktree                          # repo root only
./bin/bootstrap-worktree kindly-e-commerce-1122   # + example app

# Hotfix (manual)
git worktree add ../laravel13-hotfix -b hotfix/bug origin/main
cd ../laravel13-hotfix && ./bin/bootstrap-worktree <slug>

# Sync feature tree after hotfix merge
git merge origin/main

# Cleanup
git worktree remove ../laravel13-hotfix
git worktree prune
```
