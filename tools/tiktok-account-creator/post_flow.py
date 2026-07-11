"""TikTok web upload — requires logged-in session."""

from __future__ import annotations

import time
from pathlib import Path

from selenium.common.exceptions import TimeoutException
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait

from accounts import Account
from bot import (
    WAIT_SECONDS,
    create_browser,
    human_pause,
    human_type,
    is_button_enabled,
    load_settings,
    log_step,
    page_has_captcha,
    safe_click,
    save_debug_screenshot,
    try_dismiss_cookies,
)
from login_flow import is_logged_in, run_login
from session_store import load_cookies, save_cookies

UPLOAD_URLS = (
    "https://www.tiktok.com/tiktokstudio/upload",
    "https://www.tiktok.com/creator-center/upload",
    "https://www.tiktok.com/upload",
)


def _wait(driver):
    return WebDriverWait(driver, WAIT_SECONDS * 2)


def _open_upload_page(driver) -> bool:
    for url in UPLOAD_URLS:
        driver.get(url)
        time.sleep(2)
        try_dismiss_cookies(driver)
        if driver.find_elements(By.CSS_SELECTOR, 'input[type="file"]'):
            return True
        if "upload" in (driver.current_url or "").lower():
            return True
    return False


def _set_video_file(driver, video_path: Path) -> bool:
    path = str(video_path.resolve())
    inputs = driver.find_elements(By.CSS_SELECTOR, 'input[type="file"]')
    if not inputs:
        # TikTok sometimes nests file input
        inputs = driver.find_elements(By.XPATH, "//input[@type='file']")
    if not inputs:
        return False
    inputs[0].send_keys(path)
    human_pause(2.0, 4.0)
    return True


def _fill_caption(driver, caption: str) -> None:
    if not caption:
        return
    editors = driver.find_elements(
        By.CSS_SELECTOR,
        'div[contenteditable="true"], div.public-DraftEditor-content, '
        '[data-e2e="caption-input"] div[contenteditable]',
    )
    if editors:
        human_type(editors[0], caption)
        human_pause(0.5, 1.0)
        return
    fields = driver.find_elements(
        By.CSS_SELECTOR, 'textarea[placeholder*="caption" i], textarea'
    )
    if fields:
        human_type(fields[0], caption)


def _click_post(driver) -> bool:
    labels = ("Post", "Publish", "Upload")
    for label in labels:
        buttons = driver.find_elements(
            By.XPATH, f"//button[contains(., '{label}')]"
        )
        for btn in buttons:
            if is_button_enabled(btn):
                safe_click(driver, btn)
                human_pause(2.0, 4.0)
                return True
    return False


def run_post(
    account: Account,
    video_path: Path,
    caption: str = "",
    *,
    relogin: bool = True,
) -> str:
    """Return success | not_logged_in | upload_failed | captcha | error."""
    if not video_path.is_file():
        log_step(f"Video not found: {video_path}")
        return "error"

    settings = load_settings()
    driver = create_browser(settings)
    try:
        restored = load_cookies(driver, account.email)
        if restored:
            log_step("Restored session cookies")
            time.sleep(2)

        if not is_logged_in(driver):
            if not relogin:
                return "not_logged_in"
            log_step("No session — logging in first")
            driver.quit()
            login_result = run_login(account, save_session=True)
            if login_result != "success":
                return f"login_{login_result}"
            driver = create_browser(settings)
            load_cookies(driver, account.email)
            time.sleep(2)

        if not is_logged_in(driver):
            return "not_logged_in"

        log_step("Opening upload page")
        if not _open_upload_page(driver):
            save_debug_screenshot(driver, "upload-page")
            return "upload_failed"

        if page_has_captcha(driver):
            return "captcha"

        log_step(f"Uploading: {video_path.name}")
        if not _set_video_file(driver, video_path):
            save_debug_screenshot(driver, "upload-no-file-input")
            return "upload_failed"

        log_step("Waiting for video processing UI")
        time.sleep(8)
        _fill_caption(driver, caption)

        log_step("Clicking Post")
        if not _click_post(driver):
            save_debug_screenshot(driver, "upload-no-post-btn")
            return "upload_failed"

        time.sleep(5)
        save_cookies(driver, account.email)
        body = driver.find_element(By.TAG_NAME, "body").text.lower()
        if any(w in body for w in ("uploaded", "published", "your video")):
            log_step("Post published")
            return "success"
        if is_logged_in(driver):
            log_step("Post submitted (confirm in TikTok UI)")
            return "success"
        save_debug_screenshot(driver, "upload-uncertain")
        return "upload_failed"
    except TimeoutException:
        save_debug_screenshot(driver, "post-timeout")
        return "error"
    except Exception as exc:
        log_step(f"Post error: {exc}")
        save_debug_screenshot(driver, "post-error")
        return "error"
    finally:
        try:
            driver.quit()
        except Exception:
            pass
