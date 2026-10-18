#!/usr/bin/env bash
# install.sh - Install win-disk-cleaner skill for Claude Code
# Usage: curl -fsSL https://raw.githubusercontent.com/orzcls/win-disk-cleaner/main/install.sh | bash

set -e

SKILL_NAME="win-disk-cleaner"
REPO_URL="https://github.com/orzcls/win-disk-cleaner.git"

# Determine install target
if [ -d ".claude/skills" ]; then
    TARGET=".claude/skills/$SKILL_NAME"
elif [ -d "$HOME/.claude/skills" ]; then
    TARGET="$HOME/.claude/skills/$SKILL_NAME"
else
    TARGET="$HOME/.claude/skills/$SKILL_NAME"
    mkdir -p "$HOME/.claude/skills"
fi

echo "==> Installing $SKILL_NAME skill..."
echo "    Target: $TARGET"

# Clone or update
if [ -d "$TARGET" ]; then
    echo "    Skill already exists, updating..."
    cd "$TARGET"
    git pull --ff-only 2>/dev/null || {
        cd -
        rm -rf "$TARGET"
        git clone --depth 1 "$REPO_URL" "$TARGET"
    }
else
    git clone --depth 1 "$REPO_URL" "$TARGET"
fi

# Remove git metadata from skill directory (optional, keeps it clean)
rm -rf "$TARGET/.git" "$TARGET/.github" "$TARGET/install.sh" "$TARGET/install.ps1" "$TARGET/README.md" "$TARGET/LICENSE"

echo ""
echo "==> Done! Skill '$SKILL_NAME' installed to: $TARGET"
echo "    Restart Claude Code to load the new skill."
echo ""
echo "    Files installed:"
find "$TARGET" -type f | sed 's/^/      /'
