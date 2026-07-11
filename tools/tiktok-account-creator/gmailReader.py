"""
Gmail IMAP helpers for TikTok signup OTP.

TikTok sends 6-digit codes from addresses like *@account.tiktok.com (see OpenInbox demos).
Codes expire in ~5–15 minutes. Rate limits after failed attempts are common on web signup.
"""

from __future__ import annotations

import email
import imaplib
import json
import re
from dataclasses import dataclass
from datetime import datetime, timezone
from email.header import decode_header
from email.utils import parsedate_to_datetime
from pathlib import Path

SETTINGS_PATH = Path(__file__).resolve().parent / "settings.json"

# Observed / documented TikTok mail sources
TIKTOK_SENDER_MARKERS = (
    "tiktok",
    "bytedance",
    "musical.ly",
    "account.tiktok",
)

TIKTOK_SUBJECT_MARKERS = (
    "verify",
    "verification",
    "code",
    "login",
    "sign up",
    "signup",
)

LEGACY_HTML_REGEX = (
    r'20px;color:\s*rgb\(22,24,35\);font-weight:\s*bold;">(\d{6})<'
)
CODE_PATTERNS = [
    LEGACY_HTML_REGEX,
    r"verification code[^\d]{0,60}(\d{6})",
    r"(\d{6})\s+is your verification code",
    r"your (?:login )?code is[^\d]{0,30}(\d{6})",
    r"use the code[^\d]{0,30}(\d{6})",
    r"enter the code[^\d]{0,40}(\d{6})",
    r"code below[^\d]{0,80}(\d{6})",
    r"font-weight:\s*bold[^>]*>(\d{6})<",
    r">\s*(\d{6})\s*<",
]

MAILBOXES = ("INBOX", '"[Gmail]/Spam"', '"[Gmail]/All Mail"')


@dataclass
class MailProbe:
    mailbox: str
    message_id: bytes
    subject: str
    sender: str
    recipients: str
    date: str
    codes: list[str]
    is_tiktok: bool


def load_settings() -> dict:
    with open(SETTINGS_PATH, encoding="utf8") as data:
        return json.load(data)


def account_email(settings: dict | None = None) -> str:
    cfg = settings or load_settings()
    return f"{cfg['email']}@{cfg['eMailEnd']}"


def _is_plausible_otp(code: str) -> bool:
    if not (code.isdigit() and len(code) == 6):
        return False
    if re.match(r"^(19|20)\d{4}$", code):
        return False
    if code in {"000000", "111111", "123456", "654321", "999999"}:
        return False
    return True


def _decode_payload(part) -> str:
    payload = part.get_payload(decode=True)
    if payload is None:
        return ""
    charset = part.get_content_charset() or "utf-8"
    return payload.decode(charset, errors="replace")


def _message_text(msg) -> str:
    chunks: list[str] = []
    if msg.is_multipart():
        for part in msg.walk():
            if part.get_content_type() in ("text/plain", "text/html"):
                chunks.append(_decode_payload(part))
    else:
        chunks.append(_decode_payload(msg))
    return "\n".join(chunks)


def _decode_header_value(value: str | None) -> str:
    if not value:
        return ""
    parts = decode_header(value)
    out = []
    for chunk, charset in parts:
        if isinstance(chunk, bytes):
            out.append(chunk.decode(charset or "utf-8", errors="replace"))
        else:
            out.append(str(chunk))
    return "".join(out)


def _recipient_headers(msg) -> str:
    headers = []
    for key in ("To", "Delivered-To", "X-Original-To", "Envelope-To", "Cc"):
        val = msg.get(key)
        if val:
            headers.append(_decode_header_value(val))
    return " ".join(headers).lower()


def _is_tiktok_message(msg, haystack: str) -> bool:
    sender = _decode_header_value(msg.get("From")).lower()
    subject = _decode_header_value(msg.get("Subject")).lower()
    blob = f"{sender} {subject} {haystack}".lower()
    if any(marker in sender for marker in TIKTOK_SENDER_MARKERS):
        return True
    if "tiktok" in blob and any(s in subject for s in TIKTOK_SUBJECT_MARKERS):
        return True
    return False


def _matches_recipient(msg, recipient: str | None) -> bool:
    if not recipient:
        return True
    needle = recipient.lower().strip()
    return needle in _recipient_headers(msg)


def _message_datetime(msg) -> datetime | None:
    raw = msg.get("Date")
    if not raw:
        return None
    try:
        dt = parsedate_to_datetime(raw)
        if dt.tzinfo is None:
            dt = dt.replace(tzinfo=timezone.utc)
        return dt
    except (TypeError, ValueError):
        return None


def _after_cutoff(msg, since_epoch: float | None) -> bool:
    if since_epoch is None:
        return True
    dt = _message_datetime(msg)
    if dt is None:
        return True
    return dt.timestamp() >= since_epoch - 120  # 2 min clock skew


def _extract_codes(text: str) -> list[str]:
    found: list[str] = []
    seen: set[str] = set()
    for pattern in CODE_PATTERNS:
        for match in re.finditer(pattern, text, re.IGNORECASE | re.MULTILINE):
            code = match.group(1)
            if _is_plausible_otp(code) and code not in seen:
                seen.add(code)
                found.append(code)
    return found


def _imap_since_clause(since_epoch: float | None) -> str | None:
    if since_epoch is None:
        return None
    return datetime.fromtimestamp(since_epoch, tz=timezone.utc).strftime("%d-%b-%Y")


def _search_queries(since_epoch: float | None) -> list[str]:
    since = _imap_since_clause(since_epoch)
    base_queries = [
        'FROM "account.tiktok"',
        'FROM "tiktok"',
        'SUBJECT "TikTok"',
        'SUBJECT "verification code"',
        'SUBJECT "verify your email"',
    ]
    if since:
        return [f'(SINCE {since} {q})' for q in base_queries]
    return [f"({q} UNSEEN)" for q in base_queries] + base_queries


def _connect() -> imaplib.IMAP4_SSL:
    cfg = load_settings()
    imap = imaplib.IMAP4_SSL("imap.gmail.com")
    imap.login(account_email(cfg), cfg["gmailPass"])
    return imap


def _safe_logout(imap: imaplib.IMAP4_SSL | None) -> None:
    if imap is None:
        return
    try:
        imap.logout()
    except (imaplib.IMAP4.abort, OSError):
        pass


def probe_inbox(
    *,
    recipient: str | None = None,
    since_epoch: float | None = None,
    limit: int = 15,
) -> list[MailProbe]:
    for attempt in range(2):
        try:
            return _probe_inbox_once(
                recipient=recipient,
                since_epoch=since_epoch,
                limit=limit,
            )
        except imaplib.IMAP4.abort:
            if attempt == 0:
                continue
            raise
    return []


def _probe_inbox_once(
    *,
    recipient: str | None = None,
    since_epoch: float | None = None,
    limit: int = 15,
) -> list[MailProbe]:
    imap = _connect()
    probes: list[MailProbe] = []
    seen_ids: set[bytes] = set()

    try:
        for mailbox in MAILBOXES:
            try:
                status, _ = imap.select(mailbox)
                if status != "OK":
                    continue
            except imaplib.IMAP4.error:
                continue

            collected: list[bytes] = []
            for query in _search_queries(since_epoch):
                try:
                    _, data = imap.search(None, query)
                except imaplib.IMAP4.error:
                    continue
                ids = data[0].split()
                for mid in reversed(ids):
                    if mid not in seen_ids:
                        seen_ids.add(mid)
                        collected.append(mid)
                if len(collected) >= limit:
                    break

            for num in collected[:limit]:
                _, fetched = imap.fetch(num, "(RFC822)")
                raw = fetched[0][1]
                msg = email.message_from_bytes(raw)
                if not _matches_recipient(msg, recipient):
                    continue
                if not _after_cutoff(msg, since_epoch):
                    continue
                subject = _decode_header_value(msg.get("Subject"))
                sender = _decode_header_value(msg.get("From"))
                body = _message_text(msg)
                haystack = f"{subject}\n{body}"
                codes = _extract_codes(haystack) if _is_tiktok_message(msg, haystack) else []
                dt = _message_datetime(msg)
                probes.append(
                    MailProbe(
                        mailbox=mailbox.strip('"'),
                        message_id=num,
                        subject=subject[:120],
                        sender=sender[:120],
                        recipients=_recipient_headers(msg)[:160],
                        date=dt.isoformat() if dt else "",
                        codes=codes,
                        is_tiktok=_is_tiktok_message(msg, haystack),
                    )
                )
    finally:
        _safe_logout(imap)

    probes.sort(key=lambda p: p.date, reverse=True)
    return probes


def getmail(recipient: str | None = None, since_epoch: float | None = None) -> str:
    probes = probe_inbox(recipient=recipient, since_epoch=since_epoch, limit=25)
    for probe in probes:
        if probe.codes:
            return probe.codes[-1]
    return ""


def delete_tiktok_verification_mail(
    recipient: str | None = None,
    since_epoch: float | None = None,
) -> int:
    """Delete only TikTok verification messages (not the whole inbox)."""
    for attempt in range(2):
        try:
            return _delete_tiktok_mail_once(
                recipient=recipient,
                since_epoch=since_epoch,
            )
        except imaplib.IMAP4.abort:
            if attempt == 0:
                continue
            raise
    return 0


def _delete_tiktok_mail_once(
    *,
    recipient: str | None = None,
    since_epoch: float | None = None,
) -> int:
    imap = _connect()
    deleted = 0

    try:
        for mailbox in MAILBOXES[:2]:
            try:
                status, _ = imap.select(mailbox)
                if status != "OK":
                    continue
            except imaplib.IMAP4.error:
                continue

            for query in _search_queries(since_epoch):
                try:
                    _, data = imap.search(None, query)
                except imaplib.IMAP4.error:
                    continue
                for num in data[0].split():
                    _, fetched = imap.fetch(num, "(RFC822)")
                    msg = email.message_from_bytes(fetched[0][1])
                    body = _message_text(msg)
                    haystack = f"{_decode_header_value(msg.get('Subject'))}\n{body}"
                    if not _is_tiktok_message(msg, haystack):
                        continue
                    if not _matches_recipient(msg, recipient):
                        continue
                    imap.store(num, "+FLAGS", "\\Deleted")
                    deleted += 1

            imap.expunge()
    finally:
        _safe_logout(imap)
    return deleted


def deletemail() -> int:
    """Backward-compatible alias — only removes TikTok verification mail now."""
    return delete_tiktok_verification_mail()


def test_imap_login() -> tuple[bool, str]:
    try:
        imap = _connect()
        imap.select("INBOX")
        imap.logout()
        return True, "IMAP login OK"
    except imaplib.IMAP4.error as exc:
        return False, str(exc)


if __name__ == "__main__":
    ok, msg = test_imap_login()
    print(msg)
    if ok:
        code = getmail()
        print("OTP:", code or "(none)")
