"""TikTok email login — password or email OTP."""

from __future__ import annotations

import time

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
    page_verification_errors,
    safe_click,
    save_debug_screenshot,
    try_dismiss_cookies,
)
from otp_provider import fetch_otp
from session_store import save_cookies

LOGIN_EMAIL_URL = "https://www.tiktok.com/login/phone-or-email/email"
LOGGED_IN_MARKERS = ("/foryou", "/following", "tiktokstudio", "/@", "islands/tiktok_web")


def _wait(driver):
    return WebDriverWait(driver, WAIT_SECONDS)


def is_logged_in(driver) -> bool:
    url = (driver.current_url or "").lower()
    if "/login" in url and "signup" not in url:
        return False
    if any(m in url for m in LOGGED_IN_MARKERS):
        return True
    # upload / profile chrome
    if driver.find_elements(By.CSS_SELECTOR, '[data-e2e="upload-icon"], a[href*="/upload"]'):
        return True
    body = driver.find_element(By.TAG_NAME, "body").text.lower()
    return "log in" not in body and "sign up" not in body[:200]


def _click_use_email_login(driver) -> None:
    for text in ("Use phone / email / username", "Use phone or email", "Log in with email"):
        buttons = driver.find_elements(By.XPATH, f"//*[contains(text(), '{text}')]")
        if buttons:
            safe_click(driver, buttons[0])
            human_pause(0.8, 1.2)
            return
    links = driver.find_elements(By.LINK_TEXT, "Log in with email")
    if links:
        safe_click(driver, links[0])
        human_pause(0.8, 1.2)


def _fill_login_email(driver, email: str) -> None:
    selectors = (
        'input[name="username"]',
        'input[placeholder*="Email" i]',
        'input[type="text"][autocomplete="username"]',
        'input[name="email"]',
    )
    for sel in selectors:
        fields = driver.find_elements(By.CSS_SELECTOR, sel)
        if fields:
            human_type(fields[0], email)
            human_pause()
            return
    field = _wait(driver).until(
        EC.visibility_of_element_located((By.CSS_SELECTOR, "input[type='text']"))
    )
    human_type(field, email)


def _fill_login_password(driver, password: str) -> None:
    field = _wait(driver).until(
        EC.visibility_of_element_located((By.CSS_SELECTOR, 'input[type="password"]'))
    )
    human_type(field, password)
    human_pause()


def _click_login_submit(driver) -> None:
    for sel in (
        'button[type="submit"]',
        'button[data-e2e="login-button"]',
    ):
        buttons = driver.find_elements(By.CSS_SELECTOR, sel)
        for btn in buttons:
            if is_button_enabled(btn):
                safe_click(driver, btn)
                return
    buttons = driver.find_elements(By.XPATH, "//button[contains(., 'Log in')]")
    if buttons:
        safe_click(driver, buttons[0])


def _try_email_code_login(driver, account: Account, since_epoch: float) -> bool:
    """Switch to email code login if password path asks for verification."""
    code_links = driver.find_elements(
        By.XPATH,
        "//*[contains(text(), 'code') or contains(text(), 'Code')]",
    )
    for link in code_links:
        txt = (link.text or "").lower()
        if "send" in txt or "email" in txt:
            safe_click(driver, link)
            human_pause(1.0, 2.0)
            break

    send_buttons = driver.find_elements(
        By.CSS_SELECTOR, 'button[data-e2e="send-code-button"]'
    )
    if send_buttons and is_button_enabled(send_buttons[0]):
        safe_click(driver, send_buttons[0])
        since_epoch = time.time()
        human_pause(2.0, 4.0)

    code = fetch_otp(recipient=account.email, since_epoch=since_epoch)
    if not code:
        return False

    otp = driver.find_elements(
        By.CSS_SELECTOR,
        'input[placeholder*="code" i], input[placeholder*="6-digit" i]',
    )
    if not otp:
        return False
    human_type(otp[0], code)
    human_pause(0.5, 1.0)
    _click_login_submit(driver)
    time.sleep(3)
    return is_logged_in(driver)


def run_login(account: Account, *, save_session: bool = True) -> str:
    """Return success | captcha | otp_required | failed | error."""
    settings = load_settings()
    driver = create_browser(settings)
    since_epoch = time.time()
    try:
        driver.get(LOGIN_EMAIL_URL)
        time.sleep(2)
        try_dismiss_cookies(driver)
        _click_use_email_login(driver)

        log_step(f"Login email: {account.email}")
        _fill_login_email(driver, account.email)
        _fill_login_password(driver, account.password)
        _click_login_submit(driver)
        time.sleep(3)

        if page_has_captcha(driver):
            save_debug_screenshot(driver, "login-captcha")
            return "captcha"

        errors = page_verification_errors(driver)
        if errors:
            log_step(f"Login errors: {', '.join(errors)}")

        if is_logged_in(driver):
            log_step("Login success (password)")
            if save_session:
                path = save_cookies(driver, account.email)
                log_step(f"Session saved: {path.name}")
            return "success"

        if _try_email_code_login(driver, account, since_epoch):
            log_step("Login success (email code)")
            if save_session:
                save_cookies(driver, account.email)
            return "success"

        save_debug_screenshot(driver, "login-failed")
        return "failed"
    except TimeoutException:
        save_debug_screenshot(driver, "login-timeout")
        return "error"
    except Exception as exc:
        log_step(f"Login error: {exc}")
        save_debug_screenshot(driver, "login-error")
        return "error"
    finally:
        try:
            driver.quit()
        except Exception:
            pass
