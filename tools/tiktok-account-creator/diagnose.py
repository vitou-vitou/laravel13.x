#!/usr/bin/env python3
"""Diagnostics for TikTok signup + Gmail IMAP (no browser automation)."""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

import policy as policy_mod
import gmailReader as gr
import proxy_auto


def cmd_policy(_: argparse.Namespace) -> int:
    print(policy_mod.POLICY_SUMMARY)
    print("\nChecklist:")
    for item in policy_mod.compliance_checklist():
        print(f"  • {item}")
    check = policy_mod.preflight(dry_run=False, argv=sys.argv)
    print(f"\nAcknowledged: {check.ok}")
    return 0


def cmd_login(_: argparse.Namespace) -> int:
    ok, msg = gr.test_imap_login()
    cfg = gr.load_settings()
    print(f"Account: {gr.account_email(cfg)}")
    print(f"App password length: {len(cfg['gmailPass'])} (expect 16)")
    print(msg)
    return 0 if ok else 1


def _safe_print(text: str) -> None:
    sys.stdout.buffer.write((text + "\n").encode("utf-8", errors="replace"))


def cmd_probe(args: argparse.Namespace) -> int:
    probes = gr.probe_inbox(
        recipient=args.recipient,
        since_epoch=args.since,
        limit=args.limit,
    )
    tiktok = [p for p in probes if p.is_tiktok]
    print(json.dumps({"total": len(probes), "tiktok": len(tiktok)}, indent=2))
    for p in probes[: args.limit]:
        _safe_print("---")
        _safe_print(f"mailbox: {p.mailbox}")
        _safe_print(f"date: {p.date}")
        _safe_print(f"from: {p.sender}")
        _safe_print(f"subject: {p.subject}")
        _safe_print(f"tiktok: {p.is_tiktok}")
        _safe_print(f"codes: {p.codes or '[]'}")
        if args.recipient:
            _safe_print(f"recipients: {p.recipients[:120]}")
    return 0


def cmd_proxy(args: argparse.Namespace) -> int:
    if getattr(args, "refresh", False):
        pool = proxy_auto.refresh_free_pool(max_valid=8)
        print(f"Refreshed free pool: {len(pool)} proxies", flush=True)
    info = proxy_auto.diagnose()
    print(json.dumps(info, indent=2))
    chain = proxy_auto.build_auto_chain(refresh_pool=getattr(args, "refresh", False))
    print("auto_chain:", [f"{c.get('source')}:{c.get('host','')}" for c in chain])
    return 0


def cmd_otp(args: argparse.Namespace) -> int:
    code = gr.getmail(recipient=args.recipient, since_epoch=args.since)
    if code:
        print(code)
        return 0
    print("(no OTP found)", file=sys.stderr)
    return 1


def main() -> int:
    parser = argparse.ArgumentParser(description="TikTok account creator — Gmail diagnostics")
    sub = parser.add_subparsers(dest="command", required=True)

    p_login = sub.add_parser("login", help="Test Gmail IMAP app password")
    p_login.set_defaults(func=cmd_login)

    p_policy = sub.add_parser("policy", help="Show TikTok policy boundary + checklist")
    p_policy.set_defaults(func=cmd_policy)

    p_proxy = sub.add_parser("proxy", help="Auto-provision Tor + free proxy pool (no prompts)")
    p_proxy.add_argument("--refresh", action="store_true", help="Re-scrape free HTTP proxies")
    p_proxy.set_defaults(func=cmd_proxy)

    p_probe = sub.add_parser("probe", help="List recent candidate verification emails")
    p_probe.add_argument("--recipient", help="Filter by alias e.g. user+123@gmail.com")
    p_probe.add_argument("--since", type=float, help="Unix epoch — only mail after this time")
    p_probe.add_argument("--limit", type=int, default=10)
    p_probe.set_defaults(func=cmd_probe)

    p_otp = sub.add_parser("otp", help="Extract latest TikTok OTP")
    p_otp.add_argument("--recipient", help="Filter by alias e.g. user+123@gmail.com")
    p_otp.add_argument("--since", type=float, help="Unix epoch — only mail after this time")
    p_otp.set_defaults(func=cmd_otp)

    args = parser.parse_args()
    return args.func(args)


if __name__ == "__main__":
    raise SystemExit(main())
