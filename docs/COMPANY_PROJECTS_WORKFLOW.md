# Company projects + laravel13.x tooling

You work on **many company repos** (Phillip, GitHub, `D:\…`). Your **agent playbook** (docs, skills, `bin/`, pocket card) lives in **laravel13.x**.

**Goal:** Use laravel13.x methods on company code **without** mixing company commits into your personal repo (or the reverse).

---

## Remember (one line)

**Code + company git = company folder. Playbook + roadmaps = laravel13.x. Skills = global Cursor (`~/.cursor/skills`).**

---

## Three setups (pick one per project)

| Setup | Where you code & push | Where agent docs live | Git conflict? |
|-------|------------------------|------------------------|---------------|
| **A — Recommended** | `D:\phillipinsurancekh\repo` (company remote) | `laravel13.x/docs/` only (roadmap, audit) | **None** |
| **B — Nested clone** | `examples/<slug>/` with its own `.git` → company remote | Same + optional `examples/<slug>/docs/NEXT_SESSION.md` | **None** if parent does not `git add` the folder |
| **C — Symlink** | Same files as A; Cursor opens `examples/<slug>` → points to `D:\…` | Roadmaps in laravel13.x | **None** |

**Avoid:** `git add` whole company app into laravel13.x **without** nested `.git` — company source would mix with your personal repo history.

---

## A — Work in company folder (simplest)

1. Clone / use `D:\phillipinsurancekh\my-project`.
2. Open **that folder** in Cursor (File → Open Folder).
3. Paste prompts from [pocket card](ZERO-MISS-97-TASK-ROADMAP-PROMPT.md#pocket-card-remember-this) with **full path**.
4. Optional: copy into company repo (commit to **company** git):
   - `.cursor/rules/company-agent.mdc` — 10 lines: path, test command, “minimal diff”
5. Keep roadmaps in laravel13.x only, e.g. `docs/my-project-97-task-roadmap.md` — reference company path inside.

**Push:** company remote only. laravel13.x unchanged.

---

## B — Nested clone under `examples/` (PGI pattern)

What you already have for `examples/pgi-agency-portal`:

- Folder has **its own** `.git`
- `origin` → company path or GitHub
- Parent `laravel13.x` sees it as **untracked** (nested repo) — safe

```bash
# From laravel13.x (example)
cd examples
git clone git@github.com:phillipinsurancekh/some-app.git some-app-uat
cd some-app-uat && git checkout uat
```

Add to **laravel13.x** `.gitignore` (optional, keeps `git status` clean):

```gitignore
# Company mirrors — nested git, not part of laravel13.x
examples/pgi-agency-portal/
examples/pgi-core-frontend-uat/
examples/your-company-app/
```

**Push company code:** inside `examples/some-app-uat`, use normal `git push` to company.

**Push playbooks:** in laravel13.x root, commit only `docs/*`, not the nested app.

---

## C — Symlink (one folder, two views)

Same files on disk; open from laravel13.x workspace when you want docs + `bin/` nearby.

**Git Bash (Windows):**

```bash
cd /d/laravel13.x/examples
ln -s /d/phillipinsurancekh/pgi-agency-portal pgi-agency-portal
```

Cursor: open `d:\laravel13.x` — agent sees `examples/pgi-agency-portal`. Git remains one repo at `D:\…`.

---

## What travels from laravel13.x → company work

| Bring | How |
|-------|-----|
| Pocket card / prompts | Paste in chat; or link `docs/ZERO-MISS-97-TASK-ROADMAP-PROMPT.md` |
| [Cursor Learn map](CURSOR_LEARN_MAP.md) | Read once; same everywhere |
| Skills (superpowers, laravel-specialist, …) | Already global — [CURSOR_SKILLS_SYNC.md](CURSOR_SKILLS_SYNC.md) |
| Roadmaps / audits | File in `laravel13.x/docs/` naming company project |
| `./bin/new-example`, `./bin/verify-example` | **Laravel greenfield in laravel13.x only** |
| Herd `.test` | PHP apps linked in Herd — company or examples path |

| Do not copy blindly | Why |
|---------------------|-----|
| Whole `examples/kindly-*` | Different product |
| Committed `.env` from laravel13.x | Secrets / wrong APP_URL |
| laravel13.x `SESSION_STATE` | Your personal MVP list, not company |

---

## Multi-project cheat sheet

| I want to… | Do |
|------------|-----|
| Fix bug on company Next.js app | Open company folder → pocket card prompt → `npm test` |
| Agent roadmap for company app | Audit → `docs/<name>-roadmap.md` in **laravel13.x** |
| Keep company code off laravel13.x git | Nested `.git` in `examples/` **or** work only in `D:\…` |
| See laravel13.x docs while coding company | Symlink **or** Cursor multi-root (Add Folder to Workspace) |
| New Laravel toy / 180+ catalog | Stay in laravel13.x `./bin/new-example` |

---

## Conflict FAQ

**Q: If I put company repo under `examples/`, will `git push` on laravel13.x publish company code?**  
A: Not if that folder has its own `.git` and you do not `git add` it to the parent. Push inside the nested repo for company.

**Q: Two remotes confused?**  
A: Always `cd` into the repo you mean before `git push`. Company = inner folder. Playbooks = laravel13.x root.

**Q: Should company projects be in laravel13.x at all?**  
A: Optional. **Required in laravel13.x:** only docs you want to keep. **Optional:** nested clone or symlink for one-window agent sessions.

---

## Suggested default for you (Phillip + many repos)

1. **Daily commits:** `D:\phillipinsurancekh\<repo>` → company GitHub.  
2. **Agent roadmaps / ZERO-MISS audits:** `d:\laravel13.x\docs\<repo>-*.md`.  
3. **Optional mirror:** `examples/<repo>-uat` with nested `.git` + line in `.gitignore`.  
4. **Cursor workspace:** open laravel13.x when planning; open company repo when shipping PRs — or symlink both.

Bookmark: [pocket card](ZERO-MISS-97-TASK-ROADMAP-PROMPT.md#pocket-card-remember-this) · [Cursor Learn](CURSOR_LEARN_MAP.md)
