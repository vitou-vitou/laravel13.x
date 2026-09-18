---
name: git-push
description: Stage all changes, commit with an auto-generated one-line message, push to the current branch. Use when the user types /git-push, says "push", "commit and push", "ship it", or "save to git".
disable-model-invocation: true
---

# git-push

Clone/sync this skill on another PC or project: see [SYNC.md](SYNC.md).

status → add → commit → push. One shell call per step. Stop at first failure, print the error, do nothing further.

Scripts live in this repo (relative to workspace root). Works on any PC after clone/pull — no hardcoded drive letter.

## Steps

1. `git status --short`
   - Empty → reply "nothing to commit", stop.
   - Show output.

2. Secrets / junk guard. If any changed path matches:
   - secrets: `secrets.env`, `*.env`, `accounts.csv`, `*.pem`, `*.key`, `id_rsa*`
   - session dumps: `debug-*.log`, `debug-*.jsonl`, `/to-questionnaire-*.md` (repo-root only)
   Stop, list them, ask "add to .gitignore or commit anyway?". Never auto-commit these.
   Do **not** treat normal tracked docs (`README.md`, `resources/**/*.md`, etc.) as junk.

3. `git add .`
   Then unstage junk if still staged: `git restore --staged -- 'debug-*.log' 'to-questionnaire-*.md' 2>/dev/null; true`

3b. Pre-commit bundle (classify + URL + strip):

    Find scripts: project `.cursor/skills/git-push/scripts/`, else user `~/.cursor/skills/git-push/scripts/`.

    `powershell -NoProfile -ExecutionPolicy Bypass -File <scripts>/pre-commit-bundle.ps1 -RepoPath .`

    Prints JSON: `{"style":"conventional","url":"https://...","trailers_stripped":1,"trailers_status":"stripped 1"}`

    - `url` present → writes `.cursor/skills/git-push/open-commits.bat`. Stage it: `git add .cursor/skills/git-push/open-commits.bat`. Keep URL for steps 5b and 6.
    - `url` empty → skip staging, skip step 5b.
    - Must run before step 4 so the bat lands in the same commit.

4. Message:
   - Slash arg given (`/git-push fix typo`) → use verbatim, skip style/JSON.
   - Else use `style` from JSON. Default `conventional` if JSON missing/malformed.
   - Generate one line from `git diff --cached --stat` + file names, ≤ 72 chars, in detected style:
     - `conventional` → `feat: add x` (types: `feat`, `fix`, `docs`, `chore`, `refactor`, `test`)
     - `imperative-caps` → `Add x`
     - `imperative-lower` → `add x`
   - Cannot infer → `update`.
   - Commit with style gate off for this slash only (AI trailers still blocked):
     - Bash / Git Bash: `COMMIT_HUMANIZER=off git commit -q -m "<msg>"`
     - PowerShell: `$env:COMMIT_HUMANIZER='off'; git commit -q -m "<msg>"; Remove-Item Env:COMMIT_HUMANIZER`

   Trailers already stripped by bundle (step 3b). No post-commit action needed.

5. `git push`
   - No upstream → `git push -u origin HEAD`.
   - Rejected (non-fast-forward) → stop, print error, suggest `git pull --rebase`. Do not force.

5b. Open the commits page: `Start-Process "<url from step 3b>"`.
    Only after a successful push — before it, the page lacks the new commit.
    No URL from step 3b → skip.

6. Report, in this order:
   - detected style on its own line (`style: <word>`) — omit when slash arg was given
   - `trailers_status` from JSON if not "clean" (e.g., `trailers: stripped 1`)
   - `<short-sha> <msg> → <remote>/<branch>`
   - `url: <url from JSON>` if present
   - one caveman WHAT/WHY line explaining the change, format: `WHAT: <thing done>. WHY: <reason>.`
     Keep it short. Fragments OK. No filler. Say what landed and why it mattered this session.

## Rules

- Never `--force`, never `--no-verify`. Never amend except via step 4b (`strip-trailers.ps1`).
- Never commit if `git status` shows a merge/rebase in progress.
- No git repo → ask "init git?" once; on yes: `git init -q` then continue.

## Performance

Bundle script (classify + URL + strip) runs once per push. Classify caches style in `.git/info/commit-style`.

- First push: ~680ms (detect + cache + URL + amend)
- Later pushes: ~280–350ms (cached classify + URL + amend)
- 3 separate scripts (old): ~460–1,050ms (3× spawn overhead)

Savings: ~180–700ms per push after first.

## Example

```
$ /git-push
 M deploy.ps1
?? test.ps1
style: conventional
trailers: stripped 1
4676dd4 feat: write result md on exit → origin/master
url: https://github.com/vitou-vitou/render_explore_and_develop_rd01/commits/master/
WHAT: write result md on exit. WHY: keep deploy run record without manual notes.
```
