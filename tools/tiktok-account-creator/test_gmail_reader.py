"""Unit tests for Gmail OTP parsing (no IMAP)."""

from gmailReader import _extract_codes, _is_plausible_otp


def test_rejects_yyyymm_fragments():
    assert not _is_plausible_otp("202510")
    assert not _is_plausible_otp("199012")


def test_accepts_random_otp():
    assert _is_plausible_otp("847291")


def test_extract_from_tiktok_style_html():
    html = """
    <p style="font-size:20px;color: rgb(22,24,35);font-weight: bold;">847291</p>
    <p>Your verification code expires in 5 minutes.</p>
    """
    codes = _extract_codes(html)
    assert "847291" in codes
    assert "202510" not in codes


def test_extract_from_plain_text():
    body = "Use the code 123456 to log in to your TikTok account."
    codes = _extract_codes(body)
    assert codes == []  # 123456 is blocklisted


def test_extract_verification_phrase():
    body = "Your verification code is 592817. It expires in 5 minutes."
    assert "592817" in _extract_codes(body)
