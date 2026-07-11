"""Load settings from tiktok-account-creator (single source of truth)."""

from __future__ import annotations

import json
import sys
from pathlib import Path

from paths import CREATOR_ROOT, SETTINGS_PATH

if str(CREATOR_ROOT) not in sys.path:
    sys.path.insert(0, str(CREATOR_ROOT))


def load_settings() -> dict:
    if not SETTINGS_PATH.is_file():
        raise FileNotFoundError(
            f"Missing {SETTINGS_PATH}. Copy settings.example.json in tiktok-account-creator."
        )
    with SETTINGS_PATH.open(encoding="utf-8") as handle:
        return json.load(handle)
