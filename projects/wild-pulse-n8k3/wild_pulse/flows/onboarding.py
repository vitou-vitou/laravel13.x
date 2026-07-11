from __future__ import annotations

import time

from ..capture import grab_frame, send_ld_key
from ..ldplayer import LdPlayer
from ..settings import Settings
from ..touch import smart_tap, tap
from ..vision import screen_state


def launch_mlbb_from_home(ld: LdPlayer, settings: Settings) -> None:
    print("onboard: tap MLBB icon")
    smart_tap(ld, settings, 1220, 200, 1.5)
    time.sleep(8)


def dismiss_android_permission(ld: LdPlayer, settings: Settings) -> None:
    print("onboard: allow android permission")
    for x, y in [(1040, 530), (980, 530), (920, 560), (850, 560)]:
        smart_tap(ld, settings, x, y, 0.45)
    time.sleep(1)


def dismiss_keymap_editor(ld: LdPlayer, settings: Settings) -> None:
    print("onboard: dismiss keymap editor prompt")
    send_ld_key("ESC")
    smart_tap(ld, settings, 800, 432, 0.7)  # Confirm (blue)
    smart_tap(ld, settings, 800, 400, 0.5)
    send_ld_key("F12")
    time.sleep(1)


def hide_keymap_overlay(ld: LdPlayer, settings: Settings) -> None:
    print("onboard: hide keymap overlay (F12)")
    send_ld_key("F12")
    time.sleep(0.8)


def dismiss_keymap_tip(ld: LdPlayer, settings: Settings) -> None:
    print("onboard: dismiss keymap tip")
    send_ld_key("F12")
    smart_tap(ld, settings, 800, 520, 0.8)
    smart_tap(ld, settings, 800, 480, 0.5)
    time.sleep(1)


def complete_character_create(ld: LdPlayer, settings: Settings) -> None:
    print("onboard: create character Okay")
    smart_tap(ld, settings, 800, 790, 1.2)
    time.sleep(5)


def ensure_mlbb_ready(ld: LdPlayer, settings: Settings, max_rounds: int = 12) -> str:
    shots = settings.screenshot_dir
    for i in range(max_rounds):
        frame = grab_frame(settings, shots / f"onboard-{i}.png")
        st = screen_state(frame[:, :, ::-1])
        print(f"onboard: round={i} state={st}")
        if st == "emulator_home":
            ld.run_app()
            time.sleep(6)
            launch_mlbb_from_home(ld, settings)
        elif st == "keymap_editor":
            dismiss_keymap_editor(ld, settings)
        elif st == "splash":
            print("onboard: waiting splash")
            time.sleep(5)
        elif st == "mlbb_overlay":
            dismiss_keymap_editor(ld, settings)
            hide_keymap_overlay(ld, settings)
            complete_character_create(ld, settings)
        elif st == "android_permission":
            dismiss_android_permission(ld, settings)
        elif st == "keymap_tip":
            dismiss_keymap_tip(ld, settings)
        elif st == "character_create":
            complete_character_create(ld, settings)
        elif st in ("lobby", "hero_select", "in_match", "character_create"):
            return st
        elif st == "loading_or_dark":
            time.sleep(4)
        else:
            smart_tap(ld, settings, 800, 450, 0.5)
            time.sleep(2)
    frame = grab_frame(settings, shots / "onboard-final.png")
    return screen_state(frame[:, :, ::-1])
