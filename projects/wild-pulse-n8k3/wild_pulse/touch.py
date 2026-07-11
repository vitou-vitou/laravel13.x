from __future__ import annotations

import time

from .ldplayer import LdPlayer
from .settings import Settings

TOUCH_SCALE = 32767


def pixel_to_virtual(px: int, py: int, settings: Settings) -> tuple[int, int]:
    vx = int(px * TOUCH_SCALE / settings.game_width)
    vy = int(py * TOUCH_SCALE / settings.game_height)
    return vx, vy


def _touch_ops(vx: int, vy: int, hold_ms: int = 50) -> list[dict]:
    return [
        {"timing": 0, "operationId": "PutMultiTouch", "points": [{"id": 1, "x": vx, "y": vy, "state": 1}]},
        {"timing": hold_ms, "operationId": "PutMultiTouch", "points": []},
        {"timing": hold_ms + 70, "operationId": "PutMultiTouch", "points": [{"id": 1, "x": vx, "y": vy, "state": 0}]},
        {"timing": hold_ms + 80, "operationId": "PutMultiTouch", "points": []},
    ]


def tap(ld: LdPlayer, settings: Settings, px: int, py: int, pause: float = 0.45) -> None:
    vx, vy = pixel_to_virtual(px, py, settings)
    ld.operaterecord(_touch_ops(vx, vy))
    time.sleep(pause)


def swipe(
    ld: LdPlayer,
    settings: Settings,
    x1: int,
    y1: int,
    x2: int,
    y2: int,
    steps: int = 8,
    pause: float = 0.35,
) -> None:
    ops: list[dict] = []
    t = 0
    vx1, vy1 = pixel_to_virtual(x1, y1, settings)
    vx2, vy2 = pixel_to_virtual(x2, y2, settings)
    ops.append({"timing": t, "operationId": "PutMultiTouch", "points": [{"id": 1, "x": vx1, "y": vy1, "state": 1}]})
    for i in range(1, steps + 1):
        t += 40
        vx = vx1 + (vx2 - vx1) * i // steps
        vy = vy1 + (vy2 - vy1) * i // steps
        ops.append({"timing": t, "operationId": "PutMultiTouch", "points": [{"id": 1, "x": vx, "y": vy, "state": 1}]})
    t += 50
    ops.append({"timing": t, "operationId": "PutMultiTouch", "points": [{"id": 1, "x": vx2, "y": vy2, "state": 0}]})
    ops.append({"timing": t + 10, "operationId": "PutMultiTouch", "points": []})
    ld.operaterecord(ops)
    time.sleep(pause)


def adb_tap(ld: LdPlayer, px: int, py: int, pause: float = 0.35) -> bool:
    out = ld.adb_shell(f"shell input tap {px} {py}")
    if "not found" in out.lower() or "error" in out.lower():
        return False
    time.sleep(pause)
    return True


def smart_tap(ld: LdPlayer, settings: Settings, px: int, py: int, pause: float = 0.45) -> None:
    if not adb_tap(ld, px, py, pause):
        tap(ld, settings, px, py, pause)
