"""Upload a short video via TikTok web studio."""

from __future__ import annotations

import sys
import time
from pathlib import Path

_TOOL = Path(__file__).resolve().parent.parent
_CREATOR = _TOOL.parent / "tiktok-account-creator"
for p in (_TOOL, _CREATOR):
    if str(p) not in sys.path:
        sys.path.insert(0, str(p))

import bot as creator_bot  # noqa: E402
from flows.login import is_logged_in, run_login  # noqa: E402
from paths import SAMPLE_VIDEO  # noqa: E402
from selenium.common.exceptions import TimeoutException  # noqa: E402
from selenium.webdriver.common.by import By  # noqa: E402
from selenium.webdriver.support import expected_conditions as EC  # noqa: E402
from selenium.webdriver.support.ui import WebDriverWait  # noqa: E402
from session_store import mark_post  # noqa: E402
from window_browser import create_window_browser  # noqa: E402

UPLOAD_URLS = (
    "https://www.tiktok.com/tiktokstudio/upload?from=web",
    "https://www.tiktok.com/upload",
    "https://www.tiktok.com/creator-center/upload",
)


def _wait(driver, seconds: int = 30) -> WebDriverWait:
    return WebDriverWait(driver, seconds)


def _find_file_input(driver):
    inputs = driver.find_elements(By.CSS_SELECTOR, 'input[type="file"]')
    for inp in inputs:
        accept = (inp.get_attribute("accept") or "").lower()
        if "video" in accept or accept == "" or "mp4" in accept:
            return inp
    return inputs[0] if inputs else None


def _set_caption(driver, caption: str) -> None:
    selectors = (
        'div[contenteditable="true"]',
        'div[data-contents="true"]',
        'textarea[placeholder*="caption" i]',
        'div[role="textbox"]',
    )
    for selector in selectors:
        fields = driver.find_elements(By.CSS_SELECTOR, selector)
        for field in fields:
            try:
                field.click()
                field.send_keys(caption)
                return
            except Exception:
                continue


def _click_post(driver) -> bool:
    labels = ("Post", "Publish", "Upload")
    for label in labels:
        buttons = driver.find_elements(By.XPATH, f"//button[contains(., '{label}')]")
        for button in buttons:
            if creator_bot.is_button_enabled(button):
                creator_bot.safe_click(driver, button)
                return True
    return False


def _upload_complete(driver) -> bool:
    url = (driver.current_url or "").lower()
    if "/video/" in url:
        return True
    body = driver.find_element(By.TAG_NAME, "body").text.lower()
    return any(
        phrase in body
        for phrase in (
            "uploaded",
            "your video is being processed",
            "video published",
            "manage your posts",
        )
    )


def run_post(
    settings: dict,
    email: str,
    password: str,
    video_path: Path,
    *,
    caption: str = "farm window test #automation",
    keep_open: bool = False,
) -> str:
    if not video_path.is_file():
        return f"error:missing_video:{video_path}"

    driver = None
    try:
        driver = create_window_browser(settings, email=email, mobile=False)
        driver.get(UPLOAD_URLS[0])
        time.sleep(3)
        creator_bot.try_dismiss_cookies(driver)

        if not is_logged_in(driver):
            driver.quit()
            login_status = run_login(settings, email, password, keep_open=False)
            if login_status != "success":
                return f"login_failed:{login_status}"
            driver = create_window_browser(settings, email=email, mobile=False)
            driver.get(UPLOAD_URLS[0])
            time.sleep(3)

        file_input = None
        for _ in range(3):
            file_input = _find_file_input(driver)
            if file_input:
                break
            for alt in UPLOAD_URLS[1:]:
                driver.get(alt)
                time.sleep(2)
                file_input = _find_file_input(driver)
                if file_input:
                    break
            if file_input:
                break
            time.sleep(2)

        if not file_input:
            creator_bot.save_debug_screenshot(driver, "post-no-file-input")
            return "no_upload_input"

        abs_path = str(video_path.resolve())
        file_input.send_keys(abs_path)
        time.sleep(5)
        _set_caption(driver, caption)
        time.sleep(2)

        if not _click_post(driver):
            creator_bot.save_debug_screenshot(driver, "post-no-button")
            return "post_button_missing"

        for _ in range(20):
            if _upload_complete(driver):
                mark_post(email)
                return "success"
            time.sleep(3)

        creator_bot.save_debug_screenshot(driver, "post-timeout")
        return "post_timeout"
    except TimeoutException:
        if driver is not None:
            creator_bot.save_debug_screenshot(driver, "post-timeout-exc")
        return "timeout"
    except Exception as exc:
        if driver is not None:
            creator_bot.save_debug_screenshot(driver, "post-error")
        return f"error:{exc!s:.120}"
    finally:
        if driver is not None and not keep_open:
            driver.quit()
