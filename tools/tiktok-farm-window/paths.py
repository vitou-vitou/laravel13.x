"""Shared paths for tiktok-farm-window."""

from __future__ import annotations

from pathlib import Path

TOOL_ROOT = Path(__file__).resolve().parent
CREATOR_ROOT = TOOL_ROOT.parent / "tiktok-account-creator"
PROFILES_DIR = TOOL_ROOT / ".profiles"
ACCOUNTS_FILE = TOOL_ROOT / "accounts.json"
SAMPLE_VIDEO = TOOL_ROOT / "assets" / "sample.mp4"
SETTINGS_PATH = CREATOR_ROOT / "settings.json"
USERS_PATH = CREATOR_ROOT / "users.txt"
