from __future__ import annotations

import time

from ..capture import grab_frame
from ..ldplayer import LdPlayer
from ..settings import Settings
from ..touch import smart_tap, swipe, tap
from ..vision import screen_state
from .onboarding import ensure_mlbb_ready


def dismiss_popups(ld: LdPlayer, settings: Settings, rounds: int = 3) -> None:
    points = [(1520, 120), (1480, 160), (1200, 200), (800, 780)]
    for _ in range(rounds):
        for x, y in points:
            smart_tap(ld, settings, x, y, 0.3)
        time.sleep(0.4)


def enter_practice_mode(ld: LdPlayer, settings: Settings) -> None:
    shots = settings.screenshot_dir
    dismiss_popups(ld, settings)

    # Battle (bottom-left)
    smart_tap(ld, settings, 190, 790, 1.5)
    grab_frame(settings, shots / "after-battle.png")

    # Training camp / vs AI — try common menu slots
    for x, y in [(380, 520), (620, 420), (800, 520), (1050, 350), (800, 600)]:
        smart_tap(ld, settings, x, y, 0.9)
        frame = grab_frame(settings, shots / f"mode-{x}-{y}.png")
        st = screen_state(frame[:, :, ::-1])
        if st in ("hero_select", "in_match"):
            return


def select_any_hero(ld: LdPlayer, settings: Settings, hero_index: int = 2) -> None:
    shots = settings.screenshot_dir
    time.sleep(2)
    cols = 5
    row, col = divmod(hero_index, cols)
    x = 200 + col * 180
    y = 280 + row * 160
    smart_tap(ld, settings, x, y, 1.0)
    grab_frame(settings, shots / "hero-picked.png")
    smart_tap(ld, settings, 1450, 820, 1.2)


def start_match(ld: LdPlayer, settings: Settings) -> None:
    shots = settings.screenshot_dir
    for x, y in [(800, 780), (1450, 820), (800, 720)]:
        smart_tap(ld, settings, x, y, 1.0)
    for wait in range(20):
        time.sleep(3)
        try:
            frame = grab_frame(settings, shots / f"loading-{wait}.png")
        except RuntimeError as e:
            print(f"start_match: capture failed — {e}")
            ld.launch()
            time.sleep(5)
            continue
        if screen_state(frame[:, :, ::-1]) == "in_match":
            print(f"start_match: in_match after {wait * 3}s")
            return
        smart_tap(ld, settings, 800, 450, 0.5)


def active_play_loop(ld: LdPlayer, settings: Settings, duration_sec: int) -> bool:
    shots = settings.screenshot_dir
    deadline = time.time() + duration_sec
    in_match_seen = 0
    cycle = 0
    while time.time() < deadline:
        cycle += 1
        swipe(ld, settings, 130, 750, 220, 650, steps=6, pause=0.15)
        smart_tap(ld, settings, 1420, 780, 0.12)
        smart_tap(ld, settings, 1280, 680, 0.12)
        smart_tap(ld, settings, 1360, 620, 0.12)
        smart_tap(ld, settings, 1480, 560, 0.15)
        swipe(ld, settings, 130, 750, 80, 820, steps=5, pause=0.15)

        if cycle % 4 == 0:
            frame = grab_frame(settings, shots / f"play-{cycle}.png")
            st = screen_state(frame[:, :, ::-1])
            print(f"play: cycle={cycle} state={st}")
            if st == "in_match":
                in_match_seen += 1
            elif st == "emulator_home" and cycle > 2:
                return False
        time.sleep(0.25)
    return in_match_seen >= 2


def flow_play_hero(settings: Settings | None = None, hero_index: int = 2) -> int:
    settings = settings or Settings.load()
    settings.screenshot_dir.mkdir(parents=True, exist_ok=True)
    ld = LdPlayer(settings)

    print("==> launch MLBB")
    ld.run_app()
    time.sleep(10)

    st = ensure_mlbb_ready(ld, settings)
    print(f"==> mlbb_ready state={st}")

    if st == "emulator_home":
        print("play: MLBB not open")
        return 2

    if st != "in_match":
        enter_practice_mode(ld, settings)
        time.sleep(2)
        try:
            frame = grab_frame(settings, settings.screenshot_dir / "pre-hero.png")
            st = screen_state(frame[:, :, ::-1])
        except RuntimeError:
            st = "unknown"
        print(f"==> pre-hero state={st}")
        if st == "hero_select":
            select_any_hero(ld, settings, hero_index=hero_index)
        start_match(ld, settings)

    frame = grab_frame(settings, settings.screenshot_dir / "in-match-check.png")
    st = screen_state(frame[:, :, ::-1])
    print(f"==> match check state={st}")

    ok = active_play_loop(ld, settings, settings.play_duration_sec)
    frame = grab_frame(settings, settings.screenshot_dir / "play-done.png")
    final = screen_state(frame[:, :, ::-1])
    print(f"==> final state={final} active_ok={ok}")

    if ok or final == "in_match":
        print("play-hero: SUCCESS — hero active play in match")
        return 0
    print("play-hero: incomplete — see tmp/screenshots")
    return 1
