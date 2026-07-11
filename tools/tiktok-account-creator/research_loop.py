#!/usr/bin/env python3
"""Preflight → adaptive batch. Goal: N accounts. Plan: rotate strategy until done."""

from __future__ import annotations

import argparse
import subprocess
import sys
from pathlib import Path

_SCRIPT_DIR = Path(__file__).resolve().parent
if str(_SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(_SCRIPT_DIR))

import policy as policy_mod
from batch import main as batch_main


def run_diagnose(command: str) -> int:
    return subprocess.run(
        [sys.executable, str(_SCRIPT_DIR / "diagnose.py"), command],
        cwd=str(_SCRIPT_DIR),
    ).returncode


def main() -> int:
    parser = argparse.ArgumentParser(description="Adaptive research loop (goal unchanged)")
    parser.add_argument("--target", type=int, default=10)
    parser.add_argument("--skip-diagnose", action="store_true")
    parser.add_argument("--ack-research-only", action="store_true")
    args, _rest = parser.parse_known_args()

    if not args.skip_diagnose:
        print("=== IMAP preflight ===", flush=True)
        if run_diagnose("login") != 0:
            return 1

    if args.ack_research_only and policy_mod.RESEARCH_ACK_FLAG not in sys.argv:
        sys.argv.append(policy_mod.RESEARCH_ACK_FLAG)

    if not policy_mod.research_acknowledged():
        print(policy_mod.POLICY_SUMMARY, flush=True)
        return 2

    if "--target" not in sys.argv:
        sys.argv.extend(["--target", str(args.target)])

    print(
        f"\n=== Adaptive batch (target {args.target}) ===\n"
        "Plan: rotate chrome_uc -> chrome_mobile -> firefox on failure;\n"
        "sleep through daily cap instead of stopping.\n",
        flush=True,
    )
    return batch_main()


if __name__ == "__main__":
    raise SystemExit(main())
