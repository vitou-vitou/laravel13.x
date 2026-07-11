from __future__ import annotations

import json
import os
from dataclasses import dataclass
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


@dataclass
class Settings:
    ldplayer_home: str
    ldplayer_index: int
    game_width: int
    game_height: int
    mlbb_package: str
    hero_name: str
    play_mode: str
    play_duration_sec: int
    screenshot_dir: Path

    @classmethod
    def load(cls, path: Path | None = None) -> "Settings":
        path = path or ROOT / "settings.json"
        if not path.exists():
            path = ROOT / "settings.example.json"
        data = json.loads(path.read_text(encoding="utf-8"))
        return cls(
            ldplayer_home=data.get("ldplayerHome", "D:/LDPlayer/LDPlayer9"),
            ldplayer_index=int(data.get("ldplayerIndex", 0)),
            game_width=int(data.get("gameWidth", 1600)),
            game_height=int(data.get("gameHeight", 900)),
            mlbb_package=data.get("mlbbPackage", "com.mobile.legends"),
            hero_name=data.get("heroName", "any"),
            play_mode=data.get("playMode", "practice"),
            play_duration_sec=int(data.get("playDurationSec", 120)),
            screenshot_dir=ROOT / data.get("screenshotDir", "tmp/screenshots"),
        )


def env_ldplayer_home() -> str | None:
    return os.environ.get("LDPLAYER_HOME")
