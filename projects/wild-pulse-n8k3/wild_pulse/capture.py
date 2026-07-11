from __future__ import annotations

import subprocess
import time
from pathlib import Path

import numpy as np
from PIL import Image

from .settings import ROOT, Settings

CAPTURE_PS1 = ROOT / "scripts" / "capture_window.ps1"


def capture_window(out_path: Path) -> bool:
    out_path.parent.mkdir(parents=True, exist_ok=True)
    win_out = str(out_path).replace("/", "\\")
    if CAPTURE_PS1.exists():
        r = subprocess.run(
            [
                "powershell.exe",
                "-NoProfile",
                "-ExecutionPolicy",
                "Bypass",
                "-File",
                str(CAPTURE_PS1),
                "-OutPath",
                win_out,
            ],
            capture_output=True,
            text=True,
        )
        if out_path.exists():
            return True
        if r.stderr:
            print(r.stderr.strip())
    return False


def crop_game_viewport(img: Image.Image, settings: Settings) -> Image.Image:
    """Crop LDPlayer chrome to 1600x900 Android viewport (left side of window)."""
    w, h = img.size
    gw, gh = settings.game_width, settings.game_height
    # Title bar ~35px; game fills left gw pixels
    title = max(0, h - gh)
    left = 0
    if w > gw + 80:
        # window includes right toolbar
        pass
    crop = img.crop((left, title, left + min(gw, w), title + min(gh, h - title)))
    if crop.size != (gw, gh):
        crop = crop.resize((gw, gh), Image.Resampling.LANCZOS)
    return crop


def send_ld_key(key: str = "F12") -> None:
    ps1 = ROOT / "scripts" / "send_key.ps1"
    if ps1.exists():
        subprocess.run(
            ["powershell.exe", "-NoProfile", "-ExecutionPolicy", "Bypass", "-File", str(ps1), "-Key", key],
            capture_output=True,
            text=True,
        )
        time.sleep(0.5)


def is_valid_emulator_frame(rgb: np.ndarray) -> bool:
    """Reject browser/web captures mistaken for LDPlayer."""
    gray = np.mean(rgb, axis=2)
    mean = float(gray.mean())
    std = float(gray.std())
    # LDPlayer.net / white web pages
    if mean > 200 and std < 45:
        return False
    # LDPlayer home yellow icon patch
    h, w = rgb.shape[:2]
    patch = rgb[int(h * 0.15) : int(h * 0.28), int(w * 0.65) : int(w * 0.80)]
    if patch.size and patch[:, :, 0].mean() > 180 and patch[:, :, 1].mean() > 150:
        return True
    # Game / dark UI
    return mean < 200 or std > 35


def grab_frame(settings: Settings, out_path: Path | None = None) -> np.ndarray:
    out = out_path or (settings.screenshot_dir / f"frame-{int(time.time())}.png")
    for attempt in range(4):
        if not capture_window(out):
            time.sleep(1)
            continue
        img = Image.open(out).convert("RGB")
        game = crop_game_viewport(img, settings)
        if is_valid_emulator_frame(np.array(game)):
            return np.array(game)
        print(f"capture: invalid frame (attempt {attempt + 1}), refocusing LDPlayer")
        time.sleep(0.8)
    raise RuntimeError("window capture failed — is LDPlayer running and focused?")
