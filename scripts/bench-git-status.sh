#!/usr/bin/env bash
#
# bench-git-status.sh — Benchmark `git status` with hyperfine.
#
# Usage:
#   scripts/bench-git-status.sh [label]
#
#   label   Optional tag for this run (e.g. "before" or "after").
#           Defaults to "run". Results are exported to
#           scripts/bench-results/git-status-<label>.{json,md}
#
# Compare two runs:
#   scripts/bench-git-status.sh before
#   # ...apply the restructure...
#   scripts/bench-git-status.sh after
#
# The JSON exports can be diffed or fed into `hyperfine`'s own tooling.

set -euo pipefail

# Resolve repo root so the script works from any working directory.
REPO_ROOT="$(git rev-parse --show-toplevel)"
LABEL="${1:-run}"

OUT_DIR="${REPO_ROOT}/scripts/bench-results"
mkdir -p "${OUT_DIR}"

JSON_OUT="${OUT_DIR}/git-status-${LABEL}.json"
MD_OUT="${OUT_DIR}/git-status-${LABEL}.md"

# Run from the repo root so we benchmark a plain `git status` with no
# quoted path args. hyperfine uses cmd.exe as its shell on Windows, which
# does not strip single quotes, so an embedded `-C '<path>'` would fail.
cd "${REPO_ROOT}"

# --warmup 5   : prime the OS / git filesystem cache before timing.
# --min-runs 20: enough samples for a stable mean/stddev.
hyperfine \
  --warmup 5 \
  --min-runs 20 \
  --command-name "git status (${LABEL})" \
  --export-json "${JSON_OUT}" \
  --export-markdown "${MD_OUT}" \
  "git status"

echo
echo "Results written to:"
echo "  ${JSON_OUT}"
echo "  ${MD_OUT}"
