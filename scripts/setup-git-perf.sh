#!/usr/bin/env bash
#
# setup-git-perf.sh — Apply git status performance tweaks to THIS repo.
#
# These settings live in .git/config, which git never versions, so this
# script is the "tracked" way to reproduce them on any clone / machine.
# Safe to run repeatedly (idempotent).
#
# Usage:
#   bash scripts/setup-git-perf.sh
#
# Wire it into Laravel's composer.json so it runs after every install:
#   "scripts": {
#     "post-install-cmd": [
#       "@php artisan package:discover --ansi",
#       "bash scripts/setup-git-perf.sh || true"
#     ]
#   }

set -euo pipefail

# Only act inside a git work tree.
if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "setup-git-perf: not a git repo, skipping."
  exit 0
fi

echo "setup-git-perf: applying local git performance config..."

# 1. Untracked cache: caches per-directory mtime so git skips re-scanning
#    unchanged directories when looking for untracked files.
if git update-index --test-untracked-cache >/dev/null 2>&1; then
  git config core.untrackedCache true
  echo "  core.untrackedCache = true"
else
  echo "  core.untrackedCache = SKIPPED (filesystem mtime test failed)"
fi

# 2. fsmonitor: background daemon watches the filesystem so git only
#    examines files that actually changed (biggest win on large trees).
git config core.fsmonitor true
echo "  core.fsmonitor      = true"

# 3. Index v4: path-compresses the index (~30-50% smaller = faster reads).
git update-index --index-version 4 >/dev/null 2>&1 || true
echo "  index.version       = 4"

# Warm the caches / start the fsmonitor daemon. The SECOND status is the
# one that benefits, so run it twice.
git status >/dev/null 2>&1 || true
git status >/dev/null 2>&1 || true

echo "setup-git-perf: done. Run 'git status' — it now uses the warm caches."
