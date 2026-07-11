from __future__ import annotations

import cv2
import numpy as np


def mean_color(bgr: np.ndarray, x: int, y: int, r: int = 12) -> tuple[float, float, float]:
    h, w = bgr.shape[:2]
    x0, x1 = max(0, x - r), min(w, x + r)
    y0, y1 = max(0, y - r), min(h, y + r)
    patch = bgr[y0:y1, x0:x1]
    if patch.size == 0:
        return 0.0, 0.0, 0.0
    m = patch.mean(axis=(0, 1))
    return float(m[0]), float(m[1]), float(m[2])


def is_emulator_home(bgr: np.ndarray) -> bool:
    """LDPlayer Android home: LDStore icon yellow patch upper area."""
    h, w = bgr.shape[:2]
    b, g, r = mean_color(bgr, int(w * 0.72), int(h * 0.20), r=22)
    return r > 170 and g > 140 and b < 80


def has_keymap_overlay(bgr: np.ndarray) -> bool:
    """Semi-transparent blue key labels over game."""
    h, w = bgr.shape[:2]
    patch = bgr[int(h * 0.35) : int(h * 0.75), int(w * 0.55) : int(w * 0.95)]
    hsv = cv2.cvtColor(patch, cv2.COLOR_BGR2HSV)
    blue = cv2.inRange(hsv, (90, 40, 80), (130, 255, 255))
    return float(blue.mean()) > 8.0


def is_character_create(bgr: np.ndarray) -> bool:
    h, w = bgr.shape[:2]
    # Orange Okay button bottom-center
    b, g, r = mean_color(bgr, int(w * 0.50), int(h * 0.88), r=28)
    title_patch = bgr[int(h * 0.12) : int(h * 0.22), int(w * 0.30) : int(w * 0.70)]
    gray = cv2.cvtColor(title_patch, cv2.COLOR_BGR2GRAY)
    bright_title = float(gray.mean()) > 120
    return bright_title and r > 150 and g > 90 and b < 90


def has_keymap_editor_prompt(bgr: np.ndarray) -> bool:
    """LDPlayer: 'Please finish editing keys before operation!'"""
    h, w = bgr.shape[:2]
    # Blue Confirm button below dialog text
    b, g, r = mean_color(bgr, int(w * 0.50), int(h * 0.48), r=22)
    blue_confirm = b > 90 and g > 70 and r < 130 and b > r
    # Dialog panel (dark gray box)
    panel = bgr[int(h * 0.36) : int(h * 0.52), int(w * 0.28) : int(w * 0.72)]
    gray = cv2.cvtColor(panel, cv2.COLOR_BGR2GRAY)
    panel_present = 40 < float(gray.mean()) < 140 and float(gray.std()) > 15
    return blue_confirm and panel_present


def has_android_permission_dialog(bgr: np.ndarray) -> bool:
    """System allow/deny dialog (contacts, storage, etc.)."""
    h, w = bgr.shape[:2]
    center = bgr[int(h * 0.30) : int(h * 0.70), int(w * 0.18) : int(w * 0.82)]
    gray = cv2.cvtColor(center, cv2.COLOR_BGR2GRAY)
    # White card on dark background
    return float(gray.mean()) > 145 and float(gray.std()) > 35


def has_keymap_tip_dialog(bgr: np.ndarray) -> bool:
    if has_android_permission_dialog(bgr):
        return False
    h, w = bgr.shape[:2]
    center = bgr[int(h * 0.38) : int(h * 0.62), int(w * 0.30) : int(w * 0.70)]
    gray = cv2.cvtColor(center, cv2.COLOR_BGR2GRAY)
    # Dark themed LDPlayer keymap tip (not white Android dialog)
    return float(gray.std()) > 45 and float(gray.mean()) < 120


def has_orange_battle_button(bgr: np.ndarray) -> bool:
    h, w = bgr.shape[:2]
    x, y = int(w * 0.12), int(h * 0.88)
    b, g, r = mean_color(bgr, x, y, r=18)
    return r > 160 and g > 80 and b < 100 and r > g


def in_hero_select(bgr: np.ndarray) -> bool:
    h, w = bgr.shape[:2]
    b, g, r = mean_color(bgr, int(w * 0.92), int(h * 0.92), r=20)
    return r > 140 and g > 100 and b < 80


def in_match_ui(bgr: np.ndarray) -> bool:
    """True in-game: minimap top-left + joystick, NOT keymap overlay."""
    if has_keymap_overlay(bgr) or is_emulator_home(bgr):
        return False
    h, w = bgr.shape[:2]
    # Minimap brown/green top-left
    mini = bgr[int(h * 0.02) : int(h * 0.18), int(w * 0.02) : int(w * 0.16)]
    mini_var = float(cv2.cvtColor(mini, cv2.COLOR_BGR2GRAY).std())
    # Joystick bottom-left
    b, g, r = mean_color(bgr, int(w * 0.10), int(h * 0.82), r=25)
    joy_dark = (b + g + r) / 3 < 115
    # Attack button bottom-right warm
    ab, ag, ar = mean_color(bgr, int(w * 0.90), int(h * 0.78), r=18)
    attack_warm = ar > 100 and ar > ab
    return mini_var > 25 and joy_dark and attack_warm


def is_splash_screen(bgr: np.ndarray) -> bool:
    h, w = bgr.shape[:2]
    center = bgr[int(h * 0.35) : int(h * 0.75), int(w * 0.20) : int(w * 0.80)]
    gray = cv2.cvtColor(center, cv2.COLOR_BGR2GRAY)
    # MLBB gold logo on black splash
    return float(gray.mean()) < 70 and float(gray.std()) > 40


def screen_state(bgr: np.ndarray) -> str:
    if is_emulator_home(bgr):
        return "emulator_home"
    if is_splash_screen(bgr):
        return "splash"
    if has_keymap_editor_prompt(bgr):
        return "keymap_editor"
    if has_android_permission_dialog(bgr):
        return "android_permission"
    if has_keymap_tip_dialog(bgr):
        return "keymap_tip"
    if is_character_create(bgr):
        return "character_create"
    if in_match_ui(bgr):
        return "in_match"
    if in_hero_select(bgr):
        return "hero_select"
    if has_orange_battle_button(bgr):
        return "lobby"
    if has_keymap_overlay(bgr):
        return "mlbb_overlay"
    gray = cv2.cvtColor(bgr, cv2.COLOR_BGR2GRAY)
    if float(gray.mean()) < 95:
        return "loading_or_dark"
    return "unknown"
