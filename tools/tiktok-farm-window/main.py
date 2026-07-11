#!/usr/bin/env python3
"""TikTok farm window — visible browser tool for signup, login, and post."""

from __future__ import annotations

import argparse
import json
import os
import sys
from pathlib import Path

_TOOL = Path(__file__).resolve().parent
_CREATOR = _TOOL.parent / "tiktok-account-creator"
os.chdir(_CREATOR)
if str(_TOOL) not in sys.path:
    sys.path.insert(0, str(_TOOL))
if str(_CREATOR) not in sys.path:
    sys.path.insert(0, str(_CREATOR))

import policy as policy_mod  # noqa: E402
from flows.login import run_login  # noqa: E402
from flows.post import run_post  # noqa: E402
from flows.signup import run_signup  # noqa: E402
from paths import SAMPLE_VIDEO  # noqa: E402
from session_store import latest_account, load_accounts  # noqa: E402
from settings_loader import load_settings  # noqa: E402
import bot as creator_bot  # noqa: E402


def cmd_probe(_: argparse.Namespace) -> int:
    settings = load_settings()
    result = creator_bot.dry_run()
    print(json.dumps(result, indent=2))
    return 0 if result.get("ok") else 1


def cmd_signup(args: argparse.Namespace) -> int:
    check = policy_mod.preflight(dry_run=False, argv=sys.argv)
    if not check.ok:
        print(check.message, flush=True)
        return 2
    settings = load_settings()
    status, email, _password = run_signup(settings, keep_open=args.keep_open)
    print(f"signup: {status} email={email}", flush=True)
    return 0 if status == "success" else 1


def cmd_login(args: argparse.Namespace) -> int:
    settings = load_settings()
    acc = _resolve_account(args)
    if not acc:
        print("No account — run signup first or pass --email/--password", flush=True)
        return 2
    status = run_login(settings, acc.email, acc.password, keep_open=args.keep_open)
    print(f"login: {status} email={acc.email}", flush=True)
    return 0 if status == "success" else 1


def cmd_post(args: argparse.Namespace) -> int:
    settings = load_settings()
    acc = _resolve_account(args)
    if not acc:
        print("No account — run signup first or pass --email/--password", flush=True)
        return 2
    video = Path(args.video) if args.video else SAMPLE_VIDEO
    status = run_post(
        settings,
        acc.email,
        acc.password,
        video,
        caption=args.caption,
        keep_open=args.keep_open,
    )
    print(f"post: {status} email={acc.email} video={video}", flush=True)
    return 0 if status == "success" else 1


def cmd_cycle(args: argparse.Namespace) -> int:
    check = policy_mod.preflight(dry_run=False, argv=sys.argv)
    if not check.ok:
        print(check.message, flush=True)
        return 2
    settings = load_settings()
    print("=== 1/3 signup ===", flush=True)
    status, email, password = run_signup(settings, use_strategy=not args.no_strategy)
    if status != "success":
        print(f"cycle stopped at signup: {status}", flush=True)
        return 1
    print("=== 2/3 login ===", flush=True)
    login_status = run_login(settings, email, password)
    if login_status != "success":
        print(f"cycle stopped at login: {login_status}", flush=True)
        return 1
    video = Path(args.video) if args.video else SAMPLE_VIDEO
    print("=== 3/3 post ===", flush=True)
    post_status = run_post(settings, email, password, video, caption=args.caption)
    if post_status != "success":
        print(f"cycle stopped at post: {post_status}", flush=True)
        return 1
    print("cycle: success (signup + login + post)", flush=True)
    return 0


def cmd_accounts(_: argparse.Namespace) -> int:
    rows = load_accounts()
    print(json.dumps([a.__dict__ for a in rows], indent=2))
    return 0


def _resolve_account(args: argparse.Namespace):
    if args.email and args.password:
        from session_store import FarmAccount

        return FarmAccount(
            email=args.email,
            password=args.password,
            created_at="",
        )
    return latest_account()


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(description="TikTok farm window (signup / login / post)")
    parser.add_argument("--keep-open", action="store_true", help="Leave browser open after flow")
    sub = parser.add_subparsers(dest="command", required=True)

    def add_research_flag(subparser: argparse.ArgumentParser) -> None:
        subparser.add_argument(
            "--ack-research-only",
            action="store_true",
            help="Required for signup/cycle (research boundary)",
        )

    p_probe = sub.add_parser("probe", help="Dry-run signup page locators")
    p_probe.set_defaults(func=cmd_probe)

    p_signup = sub.add_parser("signup", help="Create one account (email OTP)")
    add_research_flag(p_signup)
    p_signup.set_defaults(func=cmd_signup)

    p_login = sub.add_parser("login", help="Log in with saved or explicit credentials")
    p_login.add_argument("--email")
    p_login.add_argument("--password")
    p_login.set_defaults(func=cmd_login)

    p_post = sub.add_parser("post", help="Upload a video")
    p_post.add_argument("--email")
    p_post.add_argument("--password")
    p_post.add_argument("--video", help="Path to mp4 (default: assets/sample.mp4)")
    p_post.add_argument("--caption", default="farm window test #tiktok")
    p_post.set_defaults(func=cmd_post)

    p_cycle = sub.add_parser("cycle", help="signup → login → post (definition of done)")
    add_research_flag(p_cycle)
    p_cycle.add_argument("--video")
    p_cycle.add_argument("--caption", default="farm window test #tiktok")
    p_cycle.add_argument(
        "--no-strategy",
        action="store_true",
        help="Skip proxy/browser rotation — use settings.json browser directly (probe parity)",
    )
    p_cycle.set_defaults(func=cmd_cycle)

    p_acc = sub.add_parser("accounts", help="List saved accounts")
    p_acc.set_defaults(func=cmd_accounts)

    return parser


def main() -> int:
    if policy_mod.RESEARCH_ACK_FLAG not in sys.argv and os.environ.get(policy_mod.RESEARCH_ACK_ENV, ""):
        sys.argv.append(policy_mod.RESEARCH_ACK_FLAG)
    parser = build_parser()
    args = parser.parse_args()
    return args.func(args)


if __name__ == "__main__":
    raise SystemExit(main())
