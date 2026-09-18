# Sync git-push skill

Self-contained skill folder. Scripts bundled. Copy once, works everywhere.

## Structure

```
.cursor/skills/git-push/
├── SKILL.md
├── SYNC.md (this file)
├── install.ps1
├── scripts/
│   ├── pre-commit-bundle.ps1   # canonical (classify + URL + strip)
│   ├── classify.ps1            # compat shim
│   ├── strip-trailers.ps1      # compat shim
│   └── commit-url.ps1          # compat shim
└── open-commits.bat (generated on first push)
```

**Source:** `git@github.com:vitou-vitou/render_explore_and_develop_rd01.git`

## Install to another project

```powershell
cd <TARGET_REPO_ROOT>
powershell -NoProfile -ExecutionPolicy Bypass -File <SOURCE>\.cursor\skills\git-push\install.ps1
```

Or user-global:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File <SOURCE>\.cursor\skills\git-push\install.ps1 -UserGlobal
```

## Agent handoff

```
TASK: Install git-push skill into this workspace.
SOURCE: git@github.com:vitou-vitou/render_explore_and_develop_rd01.git
RUN: powershell -NoProfile -ExecutionPolicy Bypass -File <SOURCE>\.cursor\skills\git-push\install.ps1 -TargetRepo .
VERIFY: /git-push on tiny change
DONE WHEN: Push succeeds, commits URL opens
```
