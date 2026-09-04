# Git `status` Performance

This repository enables two local Git features that make `git status` (and
other working-tree operations) substantially faster on large checkouts:

- **`core.fsmonitor`** — a background daemon watches the filesystem, so Git
  only inspects files that actually changed instead of stat-ing the entire
  working tree.
- **`core.untrackedCache`** — caches per-directory mtimes so Git can skip
  re-scanning unchanged directories when looking for untracked files.

## Why it matters here

This is a large working tree, which is exactly where these features pay off:

- Tracked files: **~8,993**
- Working-tree files: **~55,870**

Without fsmonitor, every `git status` stat-walks all ~55k files.

## Benchmark results

Measured with [`hyperfine`](https://github.com/sharkdp/hyperfine)
(`--warmup 10 --min-runs 30`) on the same machine, warm caches:

| Configuration | Mean | Min–Max | User CPU | System CPU |
|:---|---:|:---:|---:|---:|
| Baseline (both OFF) | 98.0 ± 12.0 ms | 83.3–126.3 ms | 161.9 ms | 145.9 ms |
| **fsmonitor + untrackedCache ON** | **71.0 ± 3.5 ms** | 65.4–79.5 ms | 29.9 ms | 31.6 ms |

**Outcome: ~27 ms faster (≈28% lower wall-clock time).** The bigger story is
CPU work, which dropped roughly 5× (User 162→30 ms, System 146→32 ms) because
Git no longer walks the whole tree. Run-to-run variance also shrank
(±12.0 → ±3.5 ms), so `git status` is both faster and steadier.

> Numbers are machine-specific and will differ on other hardware. Re-run the
> benchmark locally to measure your own delta.

## How it is applied

The settings live in `.git/config`, which Git never versions, so they cannot
be committed directly. Instead, `scripts/setup-git-perf.sh` is the tracked,
idempotent recipe that applies them, and it is wired into Composer's
`post-autoload-dump` hook — so every `composer install` / `composer update`
re-applies it automatically on any clone or machine.

To apply manually:

```bash
bash scripts/setup-git-perf.sh
```

## How to reproduce the benchmark

```bash
# Baseline (optimizations off)
git config core.fsmonitor false && git fsmonitor--daemon stop 2>/dev/null
git config core.untrackedCache false
git status >/dev/null            # settle
hyperfine --warmup 10 --min-runs 30 'git status'

# Optimized (optimizations on)
git config core.fsmonitor true && git config core.untrackedCache true
git status >/dev/null && git status >/dev/null   # warm + start daemon
hyperfine --warmup 10 --min-runs 30 'git status'
```

A helper script, `scripts/bench-git-status.sh`, wraps the optimized run and
exports JSON/Markdown into `scripts/bench-results/` (gitignored, since the
numbers are throwaway artifacts).

## Requirements

- Git **2.37+** for the built-in fsmonitor daemon (this repo was verified on
  Git 2.51). No external Watchman install is needed.
- A filesystem that passes `git update-index --test-untracked-cache` (the
  setup script checks this and skips the untracked cache if it fails).

## Reverting

```bash
git config --unset core.fsmonitor
git config --unset core.untrackedCache
git fsmonitor--daemon stop
```
