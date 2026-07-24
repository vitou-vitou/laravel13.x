#!/bin/sh
# Install repo commit-msg / prepare-commit-msg hooks (no global git config).
# Copies into .git/hooks so AI commit messages are blocked locally.
set -e
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOOKS="$ROOT/.git/hooks"
SRC="$ROOT/.githooks"

if [ ! -d "$ROOT/.git" ]; then
  echo "Not a git repo: $ROOT" >&2
  exit 1
fi

mkdir -p "$HOOKS"
cp "$SRC/commit-msg" "$HOOKS/commit-msg"
cp "$SRC/prepare-commit-msg" "$HOOKS/prepare-commit-msg"
chmod +x "$HOOKS/commit-msg" "$HOOKS/prepare-commit-msg" "$SRC/commit-msg" "$SRC/prepare-commit-msg" 2>/dev/null || true

echo "Installed:"
echo "  $HOOKS/commit-msg"
echo "  $HOOKS/prepare-commit-msg"
echo "AI / templated commit messages will be blocked."
