import email
import imaplib
import json
import re
from email.header import decode_header

with open("settings.json", encoding="utf8") as data:
    lines = json.load(data)

email_address = lines["email"] + "@" + lines["eMailEnd"]
password = lines["gmailPass"]

LEGACY_REGEX = r'20px;color: rgb\(22,24,35\);font-weight: bold;">(.*?)<\/p'
CODE_PATTERNS = [
    LEGACY_REGEX,
    r"verification code[^\d]{0,40}(\d{6})",
    r"(\d{6})\s+is your verification code",
    r"code[^\d]{0,20}(\d{6})",
    r">\s*(\d{6})\s*<",
    r"\b(\d{6})\b",
]

MAILBOXES = ("INBOX", '"[Gmail]/Spam"', '"[Gmail]/All Mail"')
MAX_GETMAIL_ATTEMPTS = 60


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


def _extract_codes(text: str) -> list[str]:
    found: list[str] = []
    for pattern in CODE_PATTERNS:
        for match in re.finditer(pattern, text, re.IGNORECASE | re.MULTILINE):
            code = match.group(1)
            if code.isdigit() and len(code) == 6:
                found.append(code)
    # Prefer codes near verification wording when generic pattern matches many numbers.
    if len(found) > 1 and "verification" in text.lower():
        scoped = []
        for pattern in CODE_PATTERNS[:-1]:
            for match in re.finditer(pattern, text, re.IGNORECASE | re.MULTILINE):
                code = match.group(1)
                if code.isdigit() and len(code) == 6:
                    scoped.append(code)
        if scoped:
            return scoped
    return found


def _search_message_ids(imap) -> list[bytes]:
    for query in (
        '(FROM "tiktok" UNSEEN)',
        '(FROM "tiktok")',
        '(SUBJECT "verification")',
        "ALL",
    ):
        _, data = imap.search(None, query)
        ids = data[0].split()
        if ids:
            return ids[-20:]
    return []


def getmail() -> str:
    imap = imaplib.IMAP4_SSL("imap.gmail.com")
    imap.login(email_address, password)
    codes: list[str] = []

    for mailbox in MAILBOXES:
        try:
            status, _ = imap.select(mailbox)
            if status != "OK":
                continue
        except imaplib.IMAP4.error:
            continue

        message_ids = _search_message_ids(imap)
        for num in reversed(message_ids):
            _, fetched = imap.fetch(num, "(RFC822)")
            raw = fetched[0][1]
            msg = email.message_from_bytes(raw)
            subject = ""
            if msg.get("Subject"):
                decoded = decode_header(msg.get("Subject"))
                subject = decoded[0][0]
                if isinstance(subject, bytes):
                    subject = subject.decode(decoded[0][1] or "utf-8", errors="replace")
            body = _message_text(msg)
            haystack = f"{subject}\n{body}"
            sender = (msg.get("From") or "").lower()
            if "tiktok" not in haystack.lower() and "tiktok" not in sender:
                continue
            codes.extend(_extract_codes(haystack))

    imap.logout()

    if not codes:
        return ""
    return codes[-1]


def deletemail() -> None:
    imap = imaplib.IMAP4_SSL("imap.gmail.com")
    imap.login(email_address, password)
    imap.select("INBOX")
    _, data = imap.search(None, "ALL")
    for num in data[0].split():
        imap.store(num, "+FLAGS", "\\Deleted")
    imap.expunge()
    print("Deleted All Mails!", flush=True)


if __name__ == "__main__":
    print(getmail())
