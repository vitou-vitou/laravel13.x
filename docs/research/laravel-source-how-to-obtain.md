# How to obtain Laravel source (official)

**Date:** 2026-09-27  
**Scope:** First-party Laravel source under [github.com/laravel](https://github.com/laravel) + Packagist `laravel/*` packages + MIT LICENSE in those repos. Not third-party Packagist packages that merely mention “laravel” in the name. Not mirror sites as authority.

**Verdict:** You do **not** scrape “all Laravel code on the internet.” Official inventory is the **Laravel GitHub org** ([https://github.com/laravel](https://github.com/laravel) — API: `public_repos: 143` as of 2026-09-27). Learn line-by-line from (1) local `vendor/laravel/framework` after `composer install`, and/or (2) `git clone` / zip of `laravel/framework` (+ optional skeleton `laravel/laravel` and other first-party repos). MIT allows study and copy when you retain the copyright notice. Mirrors/forks outside that org are secondary and often stale.

---

## A. What “all Laravel source” actually means

| Layer | What it is | Canonical location | Notes |
|---|---|---|---|
| **(1) App skeleton** | Empty/starter app (`app/`, `routes/`, `config/`, …) | [laravel/laravel](https://github.com/laravel/laravel) · Packagist `laravel/laravel` (`type: project`) | README: use this to *build* an app; framework core lives elsewhere. ([README](https://github.com/laravel/laravel/blob/13.x/README.md)) |
| **(2) Framework (Illuminate)** | Core framework code | [laravel/framework](https://github.com/laravel/framework) · Packagist `laravel/framework` | README: “This repository contains the **core code** of the Laravel framework.” Illuminate components live under `src/Illuminate/…`. ([README](https://github.com/laravel/framework/blob/13.x/README.md); [composer.json](https://github.com/laravel/framework/blob/13.x/composer.json) `replace` lists `illuminate/*` as `self.version`) |
| **(3) First-party ecosystem** | Official packages (docs tools, auth starters, ops, etc.) | Org inventory: [github.com/laravel](https://github.com/laravel) | Examples (non-exhaustive, all under org): `breeze`, `jetstream`, `sanctum`, `cashier-stripe` (Packagist name `laravel/cashier`), `horizon`, `telescope`, `octane`, `pint`, `sail`, `passport`, `dusk`, `fortify`, `reverb`, `pulse`, `pennant`, `tinker`, `prompts`, … |
| **(4) Docs repo** | Markdown docs rendered at laravel.com | [laravel/docs](https://github.com/laravel/docs) | **Not** on Packagist (`laravel/docs` → 404). Browse: [laravel.com/docs](https://laravel.com/docs) |
| **(5) Not in scope** | Random Packagist / GitHub repos with “laravel” in the name | — | Community packages ≠ first-party Laravel. Stick to `github.com/laravel` + Packagist owners that point VCS back there. |

**Org as inventory:** GitHub org page + REST list. Observed 2026-09-27 via [https://api.github.com/orgs/laravel](https://api.github.com/orgs/laravel): `public_repos: 143`, `html_url: https://github.com/laravel`, `blog: https://laravel.com`, `is_verified: true`. Paginated list: [https://api.github.com/orgs/laravel/repos](https://api.github.com/orgs/laravel/repos) (GitHub REST: [List organization repositories](https://docs.github.com/en/rest/repos/repos#list-organization-repositories)).

**Illuminate vs `vendor/illuminate/`:** Modern installs do **not** need a separate `vendor/illuminate/` tree. `laravel/framework` **replaces** the `illuminate/*` packages (see framework `composer.json` `replace`). Namespace code is at `vendor/laravel/framework/src/Illuminate/…`.

**Learner shortcut:** A normal app `composer install` already drops readable framework source under `vendor/laravel/`. No second clone required to read line-by-line.

---

## B. Official ways to obtain

### 1. Clone / download from GitHub

Primary hosts: [https://github.com/laravel/framework](https://github.com/laravel/framework), [https://github.com/laravel/laravel](https://github.com/laravel/laravel), [https://github.com/laravel/docs](https://github.com/laravel/docs).

```bash
# Framework core (pick branch matching major version)
git clone https://github.com/laravel/framework.git
cd framework && git checkout 13.x

# Or pin an exact release tag (tags exist, e.g. v13.6.0 … v13.33.0 observed 2026-09-27)
git clone --branch v13.6.0 --depth 1 https://github.com/laravel/framework.git

# App skeleton
git clone --branch 13.x https://github.com/laravel/laravel.git

# Docs (Markdown source)
git clone --branch 13.x https://github.com/laravel/docs.git
```

**Zip download (no git):** GitHub archive URLs (HTTP 302 → codeload), e.g.:

- Tag: `https://github.com/laravel/framework/archive/refs/tags/v13.6.0.zip`
- Branch: `https://github.com/laravel/framework/archive/refs/heads/13.x.zip`

UI: each repo → green **Code** → **Download ZIP**.

Branches for majors follow the `N.x` pattern (`13.x`, `12.x`, …) on framework/docs/skeleton; Packagist also publishes `13.x-dev`, `12.x-dev`, etc. ([packagist.org/packages/laravel/framework](https://packagist.org/packages/laravel/framework)).

### 2. Composer (`create-project` / `require` → `vendor/`)

**Current official app create path (docs 13.x):** install PHP/Composer/installer, then:

```shell
composer global require laravel/installer
laravel new example-app
```

Source: [laravel/docs `13.x` installation.md](https://github.com/laravel/docs/blob/13.x/installation.md) · rendered [laravel.com/docs/13.x/installation](https://laravel.com/docs/13.x/installation).

**Composer create-project** (still valid against Packagist `laravel/laravel`; documented in older official docs, e.g. 9.x):

```shell
composer create-project laravel/laravel:^9.0 example-app
```

Source: [laravel/docs `9.x` installation.md](https://github.com/laravel/docs/blob/9.x/installation.md). For current majors, prefer docs’ `laravel new` / version constraint matching Packagist.

**Require framework (or other first-party packages) into an existing app:**

```shell
composer require laravel/framework
# examples of other first-party packages (VCS on github.com/laravel):
composer require laravel/sanctum laravel/horizon laravel/pint
```

After install/update, read:

- `vendor/laravel/framework/…`
- other `vendor/laravel/<package>/…`

Packagist metadata for framework points VCS at `https://github.com/laravel/framework.git`, license **MIT** (e.g. version `v13.6.0` and `13.x-dev`). ([packagist.org/packages/laravel/framework.json](https://packagist.org/packages/laravel/framework.json))

### 3. List / bulk-clone all org repos

**GitHub CLI** (if installed; CLI is GitHub’s first-party tool, not a Laravel doc):

```bash
gh repo list laravel --limit 200
```

**GitHub REST API** (no `gh` required) — primary:

```bash
# page through; org reported 143 public repos (2026-09-27)
curl -sL "https://api.github.com/orgs/laravel/repos?per_page=100&page=1"
curl -sL "https://api.github.com/orgs/laravel/repos?per_page=100&page=2"
```

Docs: [List organization repositories](https://docs.github.com/en/rest/repos/repos#list-organization-repositories).

**Illustrative bulk-clone recipes** (not from Laravel docs — agent-authored helpers using the official API/`gh`):

Bash:

```bash
# illustrative — uses GitHub API + git clone
for page in 1 2; do
  curl -sL "https://api.github.com/orgs/laravel/repos?per_page=100&page=$page" \
    | python -c "import sys,json; [print(r['clone_url']) for r in json.load(sys.stdin)]"
done | while read url; do git clone "$url"; done
```

Windows PowerShell:

```powershell
# illustrative — uses GitHub API + git clone
$repos = @(); $page = 1
do {
  $batch = Invoke-RestMethod "https://api.github.com/orgs/laravel/repos?per_page=100&page=$page"
  $repos += $batch; $page++
} while ($batch.Count -eq 100)

foreach ($r in $repos) {
  git clone $r.clone_url
}
```

With `gh` (when available): `gh repo list laravel --limit 200 --json name,url` then clone each `url`.

Org listing includes archived repos (e.g. `homestead`, `lumen`) and a few forks — filter if you only want active first-party libraries.

### 4. Browse online without cloning

| Method | URL pattern | Primary? |
|---|---|---|
| GitHub UI | `https://github.com/laravel/<repo>` | Yes |
| Raw file | `https://raw.githubusercontent.com/laravel/framework/v13.6.0/src/Illuminate/Foundation/Application.php` | Yes (GitHub) |
| Packagist → VCS | e.g. [packagist.org/packages/laravel/framework](https://packagist.org/packages/laravel/framework) → repository link | Yes |
| Docs site | [laravel.com/docs](https://laravel.com/docs) (rendered from `laravel/docs`) | Yes |

### 5. License (MIT — study + copy with notice)

Upstream framework LICENSE (branch `13.x`):

> The MIT License (MIT)  
> Copyright (c) Taylor Otwell  
> Permission is hereby granted … to deal in the Software without restriction, including without limitation the rights to **use, copy, modify, merge, publish, distribute** …  
> The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

Sources:

- [https://raw.githubusercontent.com/laravel/framework/13.x/LICENSE.md](https://raw.githubusercontent.com/laravel/framework/13.x/LICENSE.md)
- Framework README “License” → MIT ([README](https://github.com/laravel/framework/blob/13.x/README.md))
- `composer.json` `"license": "MIT"` ([framework composer.json](https://github.com/laravel/framework/blob/13.x/composer.json))
- Same MIT header pattern on other first-party packages checked (e.g. [sanctum LICENSE.md](https://raw.githubusercontent.com/laravel/sanctum/4.x/LICENSE.md))

**Practical meaning for learners:** You may study and copy lines for learning/projects; keep the copyright + permission notice when you redistribute substantial portions. MIT does **not** mean “ignore attribution.”

---

## C. Practical learning recipe (minimal)

1. **Read what you already have:** After `composer install` in an app, open `vendor/laravel/framework/src/Illuminate/…` (and any other `vendor/laravel/*` you required). Match major version to your app’s constraint.
2. **Optional — clone framework** at the same version/tag as the app (`13.x` or `vX.Y.Z`) for history, tests, and PRs: `git clone` / zip from [laravel/framework](https://github.com/laravel/framework).
3. **Optional — clone skeleton** [laravel/laravel](https://github.com/laravel/laravel) separately to compare app wiring vs framework internals.
4. **Optional — bulk-clone** first-party org repos via `gh repo list laravel` or the GitHub org repos API (recipes above are illustrative). Prefer active, non-fork repos.
5. **Docs:** clone or browse [laravel/docs](https://github.com/laravel/docs) / [laravel.com/docs](https://laravel.com/docs).

**Warnings:**

- Cloning every fork/mirror on the internet is **neither necessary nor official**. Prefer [github.com/laravel](https://github.com/laravel).
- Third-party “Laravel” Packagist packages are not the framework.
- Archived org repos (Homestead, Lumen app skeleton, etc.) are historical; check `archived` on the API/UI before treating as current.

---

## D. Local workspace note (`d:\laravel13.x`)

Evidence from this clone (2026-09-27) — **local only**; upstream URLs above remain the claim authority.

| Fact | Local evidence |
|---|---|
| App requires `laravel/framework` ^13 | `d:\laravel13.x\composer.json` (`"laravel/framework": "^13.0"`, `"license": "MIT"`) |
| Locked / running framework | `composer.lock` → `laravel/framework` **v13.6.0**; `Application::VERSION = '13.6.0'` in `vendor/laravel/framework/src/Illuminate/Foundation/Application.php` |
| MIT text present | `vendor/laravel/framework/LICENSE.md` (Taylor Otwell / MIT) |
| Vendor first-party packages present | `vendor/laravel/`: `framework`, `pail`, `passport`, `pint`, `prompts`, `serializable-closure`, `tinker` |
| No separate `vendor/illuminate/` | Illuminate code under `vendor/laravel/framework/src/Illuminate/` (framework `replace` of `illuminate/*`) |

Upstream skeleton `laravel/laravel` `13.x` `composer.json` (fetched 2026-09-27) requires `"laravel/framework": "^13.17"` — this workspace may lag the latest 13.x patch line; pin clones/tags to **your** lockfile version when studying.

---

## Sources checklist (primary)

| Source | URL |
|---|---|
| Org | https://github.com/laravel · https://api.github.com/orgs/laravel |
| Framework | https://github.com/laravel/framework · LICENSE.md · README · composer.json |
| Skeleton | https://github.com/laravel/laravel |
| Docs repo | https://github.com/laravel/docs |
| Docs install | https://laravel.com/docs/13.x/installation · https://github.com/laravel/docs/blob/13.x/installation.md |
| Packagist framework | https://packagist.org/packages/laravel/framework |
| GitHub list-org-repos API | https://docs.github.com/en/rest/repos/repos#list-organization-repositories |
| Local | `d:\laravel13.x\composer.json`, `composer.lock`, `vendor/laravel/framework/…` |

**Secondary (do not use as truth for “what is Laravel”):** random blogs, Stack Overflow, unofficial mirrors, forks outside the Laravel org.
