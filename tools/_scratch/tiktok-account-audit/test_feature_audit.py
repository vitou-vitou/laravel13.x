"""Static feature audit for TikTok account-creation repos (no live browser/signup)."""

from __future__ import annotations

import re
from pathlib import Path

import pytest

ROOT = Path(__file__).parent

REPOS = {
    "Andromeda606": ROOT / "Andromeda606-TikTok-AccountCreator",
    "oomogo2000": ROOT / "oomogo2000-AutoAccountTiktok",
}

RULE_FEATURES = [
    "python",
    "selenium",
    "undetected_chromedriver",
    "pixel5_emulation",
    "human_like_interactions",
    "imap_otp",
    "google_sheets_logging",
    "tiktok_onboarding_flow",
]


def repo_text(repo_key: str) -> str:
    repo = REPOS[repo_key]
    parts: list[str] = []
    for path in repo.rglob("*.py"):
        if ".git" in path.parts:
            continue
        parts.append(path.read_text(encoding="utf-8", errors="replace"))
    return "\n".join(parts)


def has_pixel5_emulation(text: str) -> bool:
    patterns = [
        r"Pixel\s*5",
        r"pixel_5",
        r"deviceName.*Pixel",
        r"mobileEmulation",
        r"deviceMetrics",
    ]
    return any(re.search(p, text, re.I) for p in patterns)


def has_human_like(text: str) -> bool:
    has_delay = bool(re.search(r"time\.sleep|sleep\(", text))
    has_random_creds = bool(
        re.search(r"random\.(randint|choice|uniform)|randomPassword", text, re.I)
    )
    has_cursor = bool(re.search(r"ActionChains|move_to_element|pause\(", text))
    return has_delay and has_random_creds and has_cursor


def has_google_sheets(text: str) -> bool:
    return bool(
        re.search(r"gspread|google\.apiclient|spreadsheets\.values|worksheet", text, re.I)
    )


@pytest.mark.parametrize("repo_key", REPOS)
def test_repo_cloned(repo_key: str) -> None:
    assert REPOS[repo_key].is_dir()
    py_files = list(REPOS[repo_key].rglob("*.py"))
    assert py_files, f"{repo_key}: no Python files"


@pytest.mark.parametrize(
    ("repo_key", "feature", "expected"),
    [
        ("Andromeda606", "python", True),
        ("Andromeda606", "selenium", True),
        ("Andromeda606", "undetected_chromedriver", False),
        ("Andromeda606", "pixel5_emulation", False),
        ("Andromeda606", "human_like_interactions", False),
        ("Andromeda606", "imap_otp", True),
        ("Andromeda606", "google_sheets_logging", False),
        ("Andromeda606", "tiktok_onboarding_flow", True),
        ("oomogo2000", "python", True),
        ("oomogo2000", "selenium", True),
        ("oomogo2000", "undetected_chromedriver", True),
        ("oomogo2000", "pixel5_emulation", False),
        ("oomogo2000", "human_like_interactions", False),
        ("oomogo2000", "imap_otp", False),
        ("oomogo2000", "google_sheets_logging", False),
        ("oomogo2000", "tiktok_onboarding_flow", True),
    ],
)
def test_rule_feature_matrix(repo_key: str, feature: str, expected: bool) -> None:
    text = repo_text(repo_key)
    actual = {
        "python": bool(list(REPOS[repo_key].rglob("*.py"))),
        "selenium": "selenium" in text,
        "undetected_chromedriver": "undetected_chromedriver" in text,
        "pixel5_emulation": has_pixel5_emulation(text),
        "human_like_interactions": has_human_like(text),
        "imap_otp": "imaplib" in text,
        "google_sheets_logging": has_google_sheets(text),
        "tiktok_onboarding_flow": "tiktok.com/signup" in text.lower(),
    }[feature]
    assert actual is expected, f"{repo_key}/{feature}: got {actual}, expected {expected}"


def test_no_repo_matches_full_rule() -> None:
    """Composite rule from prior session — no single clone satisfies all features."""
    for repo_key in REPOS:
        text = repo_text(repo_key)
        full = all(
            [
                "selenium" in text,
                "undetected_chromedriver" in text,
                has_pixel5_emulation(text),
                has_human_like(text),
                "imaplib" in text,
                has_google_sheets(text),
                "tiktok.com/signup" in text.lower(),
            ]
        )
        assert full is False, f"{repo_key} unexpectedly matches full rule"
