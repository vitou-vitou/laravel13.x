#!/usr/bin/env python3
"""TikTok core CLI — signup, login, post, pipeline."""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

_SCRIPT_DIR = Path(__file__).resolve().parent
if str(_SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(_SCRIPT_DIR))

import policy as policy_mod
from accounts import get_account, get_latest_local_account, load_accounts
from bot import dry_run, run_one_signup
from login_flow import run_login
from post_flow import run_post
from session_store import has_session


def cmd_accounts(_: argparse.Namespace) -> int:
    accounts = load_accounts()
    if not accounts:
        print("No accounts in users.txt (or scratch copy).", flush=True)
        return 1
    for i, acc in enumerate(accounts):
        sess = "session" if has_session(acc.email) else "no-session"
        print(f"{i}\t{acc.email}\t{sess}")
    return 0


def cmd_signup(args: argparse.Namespace) -> int:
    if args.dry_run:
        print(json.dumps(dry_run(), indent=2))
        return 0
    if not policy_mod.research_acknowledged(sys.argv):
        if args.ack_research_only:
            sys.argv.append(policy_mod.RESEARCH_ACK_FLAG)
        else:
            print(policy_mod.POLICY_SUMMARY, flush=True)
            return 2
    result = run_one_signup()
    print(f"signup: {result}", flush=True)
    return 0 if result == "success" else 1


def cmd_login(args: argparse.Namespace) -> int:
    acc = get_account(email=args.email, index=args.index)
    if not acc:
        print("Account not found — add email:password to users.txt", flush=True)
        return 1
    result = run_login(acc)
    print(f"login: {result}", flush=True)
    return 0 if result == "success" else 1


def cmd_post(args: argparse.Namespace) -> int:
    acc = get_account(email=args.email, index=args.index)
    if not acc:
        print("Account not found", flush=True)
        return 1
    video = Path(args.video)
    result = run_post(acc, video, caption=args.caption or "")
    print(f"post: {result}", flush=True)
    return 0 if result == "success" else 1


def cmd_pipeline(args: argparse.Namespace) -> int:
    """signup → login → post (when video provided)."""
    steps: list[str] = []
    if args.signup:
        rc = cmd_signup(argparse.Namespace(dry_run=False, ack_research_only=True))
        steps.append(f"signup={rc}")
        if rc != 0:
            print(json.dumps({"pipeline": steps}, indent=2))
            return rc

    acc = get_account(email=args.email, index=args.index)
    if not acc and args.signup:
        acc = get_latest_local_account()
    if not acc:
        print("No account for login/post", flush=True)
        return 1

    login_rc = run_login(acc)
    steps.append(f"login={login_rc}")
    if login_rc != "success":
        print(json.dumps({"pipeline": steps}, indent=2))
        return 1

    if args.video:
        post_rc = run_post(acc, Path(args.video), caption=args.caption or "")
        steps.append(f"post={post_rc}")
        print(json.dumps({"pipeline": steps, "account": acc.email}, indent=2))
        return 0 if post_rc == "success" else 1

    print(json.dumps({"pipeline": steps, "account": acc.email}, indent=2))
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description="TikTok signup / login / post")
    sub = parser.add_subparsers(dest="command", required=True)

    p_acc = sub.add_parser("accounts", help="List saved accounts")
    p_acc.set_defaults(func=cmd_accounts)

    p_signup = sub.add_parser("signup", help="Email signup + Gmail OTP")
    p_signup.add_argument("--dry-run", action="store_true")
    p_signup.add_argument("--ack-research-only", action="store_true")
    p_signup.set_defaults(func=cmd_signup)

    p_login = sub.add_parser("login", help="Email login; saves session cookies")
    p_login.add_argument("--email", help="Account email from users.txt")
    p_login.add_argument("--index", type=int, default=0)
    p_login.set_defaults(func=cmd_login)

    p_post = sub.add_parser("post", help="Upload video (login/session required)")
    p_post.add_argument("--video", required=True, help="Path to .mp4")
    p_post.add_argument("--caption", default="", help="Post caption")
    p_post.add_argument("--email", help="Account email")
    p_post.add_argument("--index", type=int, default=0)
    p_post.set_defaults(func=cmd_post)

    p_pipe = sub.add_parser("pipeline", help="signup + login + optional post")
    p_pipe.add_argument("--signup", action="store_true", help="Run signup first")
    p_pipe.add_argument("--video", help="Optional video for post step")
    p_pipe.add_argument("--caption", default="")
    p_pipe.add_argument("--email")
    p_pipe.add_argument("--index", type=int, default=0)
    p_pipe.set_defaults(func=cmd_pipeline)

    args = parser.parse_args()
    return args.func(args)


if __name__ == "__main__":
    raise SystemExit(main())
