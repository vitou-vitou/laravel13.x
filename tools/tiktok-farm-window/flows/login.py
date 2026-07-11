"""TikTok email login in a visible browser window."""

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
from selenium.common.exceptions import TimeoutException  # noqa: E402
from selenium.webdriver.common.by import By  # noqa: E402
from selenium.webdriver.support import expected_conditions as EC  # noqa: E402
from selenium.webdriver.support.ui import WebDriverWait  # noqa: E402
from session_store import mark_login  # noqa: E402
from window_browser import create_window_browser  # noqa: E402

LOGIN_URL = "https://www.tiktok.com/login/phone-or-email/email"
LOGGED_IN_MARKERS = ("/foryou", "/following", "tiktok.com/@", "/upload")


def _wait(driver, seconds: int = 20) -> WebDriverWait:
    return WebDriverWait(driver, seconds)


def _dismiss_cookies(driver) -> None:
    creator_bot.try_dismiss_cookies(driver)


def _click_use_email_if_needed(driver) -> None:
    for label in ("Use phone or email", "Log in with email", "Email"):
        buttons = driver.find_elements(By.XPATH, f"//*[contains(text(), '{label}')]")
        for button in buttons[:3]:
            try:
                creator_bot.safe_click(driver, button)
                time.sleep(1)
                return
            except Exception:
                continue


def _fill_login_form(driver, email: str, password: str) -> None:
    email_input = _wait(driver).until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, 'input[name="username"], input[type="text"][placeholder*="Email" i]')
        )
    )
    creator_bot.human_type(email_input, email)
    password_input = _wait(driver).until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, 'input[type="password"][autocomplete="current-password"], input[type="password"]')
        )
    )
    creator_bot.human_type(password_input, password)


def _click_login_submit(driver) -> None:
    for selector in (
        'button[type="submit"]',
        'button[data-e2e="login-button"]',
    ):
        buttons = driver.find_elements(By.CSS_SELECTOR, selector)
        for button in buttons:
            if creator_bot.is_button_enabled(button):
                creator_bot.safe_click(driver, button)
                return
    buttons = driver.find_elements(By.XPATH, "//button[contains(., 'Log in')]")
    if buttons:
        creator_bot.safe_click(driver, buttons[0])


def is_logged_in(driver) -> bool:
    url = (driver.current_url or "").lower()
    if any(marker in url for marker in LOGGED_IN_MARKERS):
        return True
    if "/login" in url:
        return False
    # Profile avatar in header
    return bool(
        driver.find_elements(By.CSS_SELECTOR, '[data-e2e="profile-icon"], a[href*="/@"]')
    )


def run_login(settings: dict, email: str, password: str, *, keep_open: bool = False) -> str:
    driver = None
    try:
        driver = create_window_browser(settings, email=email, mobile=False)
        driver.get(LOGIN_URL)
        time.sleep(2)
        _dismiss_cookies(driver)
        _click_use_email_if_needed(driver)
        _fill_login_form(driver, email, password)
        _click_login_submit(driver)
        time.sleep(4)

        if is_logged_in(driver):
            mark_login(email)
            return "success"

        body = driver.find_element(By.TAG_NAME, "body").text
        if "Verify" in body or "verification" in body.lower():
            return "verification_required"
        if "incorrect" in body.lower() or "wrong password" in body.lower():
            return "bad_credentials"
        creator_bot.save_debug_screenshot(driver, "login-incomplete")
        return "incomplete"
    except TimeoutException:
        if driver is not None:
            creator_bot.save_debug_screenshot(driver, "login-timeout")
        return "timeout"
    except Exception as exc:
        if driver is not None:
            creator_bot.save_debug_screenshot(driver, "login-error")
        return f"error:{exc!s:.120}"
    finally:
        if driver is not None and not keep_open:
            driver.quit()
