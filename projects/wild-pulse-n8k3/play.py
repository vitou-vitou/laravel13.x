#!/usr/bin/env python3
"""wild-pulse-n8k3 CLI — LDPlayer MLBB active-play controller."""

from __future__ import annotations

import argparse
import sys
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parent
sys.path.insert(0, str(ROOT))

from wild_pulse.adb import AdbBridge
from wild_pulse.flows.play_hero import flow_play_hero
from wild_pulse.flows.preflight import flow_preflight, install_mlbb, ensure_ldplayer
from wild_pulse.ldplayer import LdPlayer
from wild_pulse.settings import Settings


def cmd_install(_: argparse.Namespace) -> int:
    settings = Settings.load()
    ld = LdPlayer(settings)
    adb = AdbBridge(ld)
    ensure_ldplayer(ld)
    time.sleep(8)
    # ADB optional — operaterecord works without it
    adb.wait_ready(15)
    ok = install_mlbb(ld, adb, settings)
    print("install: ok" if ok else "install: failed")
    return 0 if ok else 1


def cmd_cycle(args: argparse.Namespace) -> int:
    code = flow_preflight()
    if code == 2:
        cmd_install(args)  # best-effort; MLBB may already be installed
    return flow_play_hero(hero_index=args.hero_index)


def main() -> int:
    parser = argparse.ArgumentParser(description="wild-pulse-n8k3 — MLBB active play on LDPlayer")
    sub = parser.add_subparsers(dest="cmd", required=True)

    sub.add_parser("preflight", help="Launch LDPlayer, check ADB + MLBB")
    sub.add_parser("install", help="Install MLBB via LDStore / installapp")
    p_play = sub.add_parser("play-hero", help="Select hero and active-play loop")
    p_play.add_argument("--hero-index", type=int, default=2, help="Hero grid index (0-based)")
    p_cycle = sub.add_parser("cycle", help="preflight → install if needed → play-hero (DoD)")
    p_cycle.add_argument("--hero-index", type=int, default=2)

    args = parser.parse_args()
    if args.cmd == "preflight":
        return flow_preflight()
    if args.cmd == "install":
        return cmd_install(args)
    if args.cmd == "play-hero":
        return flow_play_hero(hero_index=args.hero_index)
    if args.cmd == "cycle":
        return cmd_cycle(args)
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
