from __future__ import annotations

import json
import os
import random
import re
import string
import sys
import tempfile
import time
from pathlib import Path

_SCRIPT_DIR = Path(__file__).resolve().parent
if str(_SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(_SCRIPT_DIR))

import policy as policy_mod
import run_log as run_log_mod
from otp_provider import fetch_otp
from proxy_config import load_proxy
import strategy as strategy_mod
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.firefox.service import Service
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait

SIGNUP_URL = "https://www.tiktok.com/signup/phone-or-email/email"
USERNAME_URL_MARKER = "/signup/create-username"

browser = None
emailFull = ""
passw = ""
browser_label = "firefox"
send_code_epoch: float | None = None
resend_count = 0
signup_complete = False
auto_restart = True
WAIT_SECONDS = 15
SEND_CODE_WAIT_SECONDS = 45
MAX_GETMAIL_ATTEMPTS = 60
RESEND_AFTER_ATTEMPTS = 18
MAX_RESENDS = 2
RESEND_COOLDOWN_SECONDS = 90

PIXEL_5_MOBILE = {
    "deviceMetrics": {"width": 393, "height": 851, "pixelRatio": 2.75},
    "userAgent": (
        "Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36"
    ),
}


def log_step(message: str) -> None:
    print(message, flush=True)


def is_button_enabled(button) -> bool:
    disabled = button.get_attribute("disabled")
    aria_disabled = button.get_attribute("aria-disabled")
    if aria_disabled and aria_disabled.lower() == "true":
        return False
    return disabled in (None, "", "false", False)


def human_pause(low: float = 0.4, high: float = 1.2) -> None:
    time.sleep(random.uniform(low, high))


def human_type(element, text: str) -> None:
    element.click()
    try:
        element.clear()
    except Exception:
        pass
    for ch in text:
        element.send_keys(ch)
        time.sleep(random.uniform(0.05, 0.18))


def safe_click(driver, element) -> None:
    """Scroll into view then click; JS fallback for TikTok overlays."""
    try:
        driver.execute_script(
            "arguments[0].scrollIntoView({block: 'center', inline: 'nearest'});",
            element,
        )
        time.sleep(0.25)
        element.click()
    except Exception:
        driver.execute_script("arguments[0].click();", element)


def load_settings() -> dict:
    with open("settings.json", encoding="utf8") as data:
        base = json.load(data)
    if strategy_mod.RUNTIME:
        merged = dict(base)
        if strategy_mod.RUNTIME.get("browser"):
            merged["browser"] = strategy_mod.RUNTIME["browser"]
        if strategy_mod.RUNTIME.get("proxy"):
            merged["proxy"] = strategy_mod.RUNTIME["proxy"]
        return merged
    return base


def _browser_mode(jsondata: dict) -> str:
    return (jsondata.get("browser") or "firefox").strip().lower()


def _apply_proxy_to_chrome(options, jsondata: dict) -> None:
    proxy = load_proxy(jsondata)
    if proxy and proxy.chrome_argument():
        options.add_argument(proxy.chrome_argument())
        log_step(f"Proxy: {proxy.scheme}://{proxy.host}:{proxy.port}")


def _create_firefox(jsondata: dict):
    global browser_label
    browser_label = "firefox"
    proxy = load_proxy(jsondata)
    profile_dir = tempfile.mkdtemp(prefix="tiktok_ff_")
    options = webdriver.FirefoxOptions()
    options.add_argument("-profile")
    options.add_argument(profile_dir)
    service = Service(executable_path=jsondata["geckoPath"])
    if proxy and proxy.enabled:
        from selenium.webdriver.common.proxy import Proxy, ProxyType

        sel_proxy = Proxy()
        if proxy.scheme == "socks5":
            sel_proxy.proxy_type = ProxyType.MANUAL
            sel_proxy.socks_proxy = f"{proxy.host}:{proxy.port}"
            sel_proxy.socks_version = 5
            if proxy.username:
                sel_proxy.socks_username = proxy.username
            if proxy.password:
                sel_proxy.socks_password = proxy.password
        else:
            sel_proxy.proxy_type = ProxyType.MANUAL
            sel_proxy.http_proxy = f"{proxy.host}:{proxy.port}"
            sel_proxy.ssl_proxy = f"{proxy.host}:{proxy.port}"
        options.proxy = sel_proxy
        log_step(f"Proxy: {proxy.scheme}://{proxy.host}:{proxy.port}")
    driver = webdriver.Firefox(service=service, options=options)
    driver.implicitly_wait(5)
    return driver


def _create_chrome_mobile(jsondata: dict, *, use_custom_driver: bool = True):
    global browser_label
    browser_label = "chrome_mobile"
    from selenium.webdriver.chrome.options import Options as ChromeOptions
    from selenium.webdriver.chrome.service import Service as ChromeService

    options = ChromeOptions()
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option("useAutomationExtension", False)
    options.add_experimental_option("mobileEmulation", PIXEL_5_MOBILE)
    _apply_proxy_to_chrome(options, jsondata)
    chrome_path = jsondata.get("chromePath") if use_custom_driver else None
    if chrome_path:
        driver = webdriver.Chrome(
            service=ChromeService(executable_path=chrome_path), options=options
        )
    else:
        driver = webdriver.Chrome(options=options)
    driver.implicitly_wait(5)
    return driver


def create_browser(jsondata: dict):
    global browser_label
    mode = _browser_mode(jsondata)
    browser_label = mode

    try:
        if mode in ("chrome", "chrome_mobile"):
            return _create_chrome_mobile(jsondata)

        if mode == "chrome_uc":
            try:
                import undetected_chromedriver as uc
            except ImportError as exc:
                log_step("chrome_uc unavailable — firefox fallback")
                return _create_firefox(jsondata)

            options = uc.ChromeOptions()
            options.add_argument(f"--user-agent={PIXEL_5_MOBILE['userAgent']}")
            options.add_argument("--window-size=393,851")
            options.add_argument("--disable-blink-features=AutomationControlled")
            _apply_proxy_to_chrome(options, jsondata)
            try:
                driver = uc.Chrome(options=options, use_subprocess=True)
                driver.implicitly_wait(5)
                browser_label = "chrome_uc"
                return driver
            except Exception:
                log_step("chrome_uc failed — trying chrome_mobile without pinned driver")
                try:
                    return _create_chrome_mobile(jsondata, use_custom_driver=False)
                except Exception:
                    log_step("chrome_mobile failed — firefox fallback")
                    return _create_firefox(jsondata)

        return _create_firefox(jsondata)
    except Exception as exc:
        log_step(f"Browser {mode} failed ({exc!s:.120}) — firefox fallback")
        return _create_firefox(jsondata)


def wait(driver: webdriver.Firefox) -> WebDriverWait:
    return WebDriverWait(driver, WAIT_SECONDS)


def try_dismiss_cookies(driver: webdriver.Firefox) -> None:
    for label in ("Decline optional cookies", "Allow all"):
        buttons = driver.find_elements(
            By.XPATH, f"//button[contains(., '{label}')]"
        )
        if buttons:
            try:
                safe_click(driver, buttons[0])
                time.sleep(0.5)
                return
            except Exception:
                pass


def select_birthday_option(driver: webdriver.Firefox, label: str, value: str) -> None:
    combo = wait(driver).until(
        EC.element_to_be_clickable(
            (By.CSS_SELECTOR, f'[role="combobox"][aria-label^="{label}"]')
        )
    )
    safe_click(driver, combo)
    option = wait(driver).until(
        EC.element_to_be_clickable(
            (
                By.XPATH,
                f'//div[@role="option" and normalize-space(text())="{value}"]',
            )
        )
    )
    safe_click(driver, option)
    human_pause(0.2, 0.5)


def fill_birthday(driver: webdriver.Firefox) -> None:
    months = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
    ]
    month = random.choice(months)
    day = str(random.randint(1, 28))
    year = str(random.randint(1990, 2000))
    select_birthday_option(driver, "Month", month)
    select_birthday_option(driver, "Day", day)
    select_birthday_option(driver, "Year", year)
    human_pause()


def fill_email(driver: webdriver.Firefox, alias_email: str) -> None:
    email_input = wait(driver).until(
        EC.visibility_of_element_located((By.CSS_SELECTOR, 'input[name="email"]'))
    )
    human_type(email_input, alias_email)
    human_pause()


def fill_password(driver: webdriver.Firefox, password: str) -> None:
    password_input = wait(driver).until(
        EC.visibility_of_element_located(
            (
                By.CSS_SELECTOR,
                'input[type="password"][autocomplete="new-password"]',
            )
        )
    )
    human_type(password_input, password)
    password_input.send_keys(Keys.TAB)
    human_pause(0.5, 1.0)


def accept_email_consent(driver: webdriver.Firefox) -> None:
    try:
        consent = wait(driver).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "#email-consent"))
        )
        if not consent.is_selected():
            driver.find_element(By.CSS_SELECTOR, 'label[for="email-consent"]').click()
    except TimeoutException:
        pass


def save_debug_screenshot(driver, label: str) -> None:
    path = _SCRIPT_DIR / f"debug-{label}-{int(time.time())}.png"
    try:
        driver.save_screenshot(str(path))
        log_step(f"Screenshot saved: {path.name}")
    except Exception:
        pass


def page_has_captcha(driver) -> bool:
    body = driver.find_element(By.TAG_NAME, "body").text.lower()
    url = (driver.current_url or "").lower()
    markers = ("captcha", "verify you are human", "security check", "puzzle")
    return any(m in body or m in url for m in markers)


def click_resend_code(driver) -> bool:
    """Click Send code again when OTP mail never arrives (bounded retries)."""
    global resend_count, send_code_epoch
    if resend_count >= MAX_RESENDS:
        return False
    errors = page_verification_errors(driver)
    if any("Maximum" in e or "Try again later" in e for e in errors):
        return False
    buttons = driver.find_elements(
        By.CSS_SELECTOR, 'button[data-e2e="send-code-button"]'
    )
    if not buttons:
        return False
    button = buttons[0]
    if not is_button_enabled(button):
        return False
    log_step(f"Resend code ({resend_count + 1}/{MAX_RESENDS}) after cooldown")
    time.sleep(RESEND_COOLDOWN_SECONDS)
    try:
        safe_click(driver, button)
    except Exception:
        driver.execute_script("arguments[0].click();", button)
    resend_count += 1
    send_code_epoch = time.time()
    human_pause(1.0, 2.0)
    return True


def send_code_was_triggered(driver) -> bool:
    body = driver.find_element(By.TAG_NAME, "body").text
    if "Enter 6-digit code" in body:
        return True
    if driver.find_elements(By.CSS_SELECTOR, 'input[placeholder="Enter 6-digit code"]'):
        return True
    buttons = driver.find_elements(By.CSS_SELECTOR, 'button[data-e2e="send-code-button"]')
    if buttons:
        label = (buttons[0].text or "").lower()
        if "resend" in label or re.search(r"\d+\s*s", label):
            return True
    return False


def click_send_code(driver: webdriver.Firefox) -> str:
    global send_code_epoch
    locator = (By.CSS_SELECTOR, 'button[data-e2e="send-code-button"]')
    send_wait = WebDriverWait(driver, SEND_CODE_WAIT_SECONDS)

    def enabled_send_code_button(driver: webdriver.Firefox):
        button = driver.find_element(*locator)
        if is_button_enabled(button):
            return button
        return False

    button = send_wait.until(enabled_send_code_button)
    safe_click(driver, button)
    send_code_epoch = time.time()
    log_step("Send code clicked")
    human_pause(2.0, 4.0)
    errors = page_verification_errors(driver)
    if errors:
        log_step(f"After send code, page shows: {', '.join(errors)}")
        if any("Maximum" in e or "Try again later" in e for e in errors):
            return "rate_limited"
    if page_has_captcha(driver):
        log_step("Captcha / security check detected — manual intervention required.")
        save_debug_screenshot(driver, "captcha")
        return "captcha"
    if not send_code_was_triggered(driver):
        log_step("Send code not acknowledged — retrying click once")
        buttons = driver.find_elements(By.CSS_SELECTOR, 'button[data-e2e="send-code-button"]')
        if buttons and is_button_enabled(buttons[0]):
            safe_click(driver, buttons[0])
            send_code_epoch = time.time()
            human_pause(2.0, 3.0)
        if not send_code_was_triggered(driver):
            save_debug_screenshot(driver, "send-code-uncertain")
            return "send_code_failed"
    log_step("Send code acknowledged by UI (OTP field or resend timer)")
    time.sleep(8)
    return "ok"


def otp_input(driver: webdriver.Firefox):
    return wait(driver).until(
        EC.visibility_of_element_located(
            (By.CSS_SELECTOR, 'input[placeholder="Enter 6-digit code"]')
        )
    )


def click_next(driver: webdriver.Firefox) -> None:
    submit = wait(driver).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, 'button[type="submit"]'))
    )
    WebDriverWait(browser, SEND_CODE_WAIT_SECONDS).until(
        lambda d: is_button_enabled(
            d.find_element(By.CSS_SELECTOR, 'button[type="submit"]')
        )
    )
    submit = browser.find_element(By.CSS_SELECTOR, 'button[type="submit"]')
    submit.click()


SIGNUP_EMAIL_PATH = "/signup/phone-or-email/email"
SUCCESS_URL_MARKERS = (
    "/signup/create-username",
    "/login/download-app",
    "/foryou",
    "islands/tiktok_web",
)


def is_signup_email_page(driver: webdriver.Firefox) -> bool:
    return SIGNUP_EMAIL_PATH in (driver.current_url or "")


def page_verification_errors(driver: webdriver.Firefox) -> list[str]:
    body = driver.find_element(By.TAG_NAME, "body").text
    phrases = (
        "Incorrect code",
        "Maximum number of attempts",
        "Try again later",
        "Verification failed",
        "Enter a valid",
    )
    return [phrase for phrase in phrases if phrase in body]


def page_has_incorrect_code(driver: webdriver.Firefox) -> bool:
    return bool(page_verification_errors(driver))


def getGmail(attempt: int = 1) -> str | None:
    global signup_complete
    if signup_complete:
        return "success"
    import gmailReader as gr

    log_step(f"getMail (attempt {attempt}/{MAX_GETMAIL_ATTEMPTS})")
    code = fetch_otp(recipient=emailFull, since_epoch=send_code_epoch)
    if not code:
        if (
            attempt == RESEND_AFTER_ATTEMPTS
            and browser is not None
            and click_resend_code(browser)
        ):
            getGmail(attempt + 1)
            return
        if attempt >= MAX_GETMAIL_ATTEMPTS:
            msg = (
                "No TikTok verification email found after "
                f"{MAX_GETMAIL_ATTEMPTS} attempts. Check Spam or wait out rate limit."
            )
            log_step(msg)
            run_log_mod.append_run(
                email_alias=emailFull,
                status="otp_timeout",
                detail=msg,
                url=_safe_url(browser),
                browser=browser_label,
            )
            if browser is not None:
                save_debug_screenshot(browser, "otp-timeout")
            return "otp_timeout"
        time.sleep(3)
        getGmail(attempt + 1)
        return None

    log_step("OTP received from email (6 digits)")
    code_path = otp_input(browser)
    if code_path.get_property("value") != code:
        try:
            code_path.clear()
        except Exception:
            print("Deletion could not be performed")
        code_path.send_keys(code)
        return Register()
    elif browser.current_url is None:
        return "error"
    else:
        return getGmail()


def _safe_url(driver: webdriver.Firefox | None) -> str:
    if driver is None:
        return ""
    try:
        return driver.current_url or ""
    except Exception:
        return ""


def random_username() -> str:
    suffix = "".join(random.choices(string.ascii_lowercase + string.digits, k=8))
    return f"user_{suffix}"


def handle_username_step(driver: webdriver.Firefox) -> bool:
    """Fill suggested username on /signup/create-username when present."""
    if USERNAME_URL_MARKER not in (driver.current_url or ""):
        return False

    inputs = driver.find_elements(
        By.CSS_SELECTOR,
        'input[name="username"], input[placeholder*="username" i]',
    )
    if not inputs:
        skip_buttons = driver.find_elements(By.XPATH, "//button[contains(., 'Skip')]")
        if skip_buttons:
            log_step("Username step — clicking Skip")
            skip_buttons[0].click()
            human_pause(0.8, 1.5)
            return True
        return False

    field = inputs[0]
    proposed = random_username()
    field.clear()
    field.send_keys(proposed)
    human_pause(0.5, 1.0)
    log_step(f"Username step — entered {proposed}")

    for label in ("Next", "Continue", "Sign up"):
        buttons = driver.find_elements(
            By.XPATH, f"//button[contains(., '{label}')]"
        )
        for button in buttons:
            if is_button_enabled(button):
                button.click()
                human_pause(1.0, 2.0)
                return True
    return False


def Register() -> str | None:
    global emailFull, passw, signup_complete
    if signup_complete:
        return "success"
    try:
        click_next(browser)
        time.sleep(2)
        errors = page_verification_errors(browser)
        if errors:
            log_step(f"Verification failed on page: {', '.join(errors)}")
            if "Maximum number of attempts" in errors or "Try again later" in errors:
                log_step("Rate limited — stop and retry later with a fresh session.")
                run_log_mod.append_run(
                    email_alias=emailFull,
                    status="rate_limited",
                    detail=", ".join(errors),
                    url=_safe_url(browser),
                    browser=browser_label,
                )
                save_debug_screenshot(browser, "rate-limit")
                return "rate_limited"
            time.sleep(3)
            return getGmail()
        if not signup_progressed(browser):
            log_step("Still on email signup step; not treating as success.")
            save_debug_screenshot(browser, "stuck-email")
            return "incomplete"
        return finish_success_if_possible()
    except Exception:
        try:
            print(browser.current_url)
        except Exception:
            return "error"
        if browser.current_url == "https://www.tiktok.com/login/download-app":
            return finish_success()
        if is_signup_email_page(browser):
            log_step("Register failed while still on email signup page.")
            return "incomplete"
        try:
            click_next(browser)
            if signup_progressed(browser):
                return finish_success_if_possible()
        except Exception:
            log_step("You did not enter the code, try again")
            time.sleep(1)
            return Register()
    return "error"


def signup_progressed(driver: webdriver.Firefox) -> bool:
    if is_signup_email_page(driver):
        return False
    url = driver.current_url or ""
    return any(marker in url for marker in SUCCESS_URL_MARKERS)


def finish_success_if_possible() -> str | None:
    if signup_complete:
        return "success"
    if is_signup_email_page(browser):
        errors = page_verification_errors(browser)
        if errors:
            log_step(f"Signup incomplete: {', '.join(errors)}")
        return "incomplete"

    if USERNAME_URL_MARKER in (_safe_url(browser)):
        handle_username_step(browser)
        human_pause(1.0, 2.0)

    skip_buttons = browser.find_elements(By.XPATH, "//button[contains(., 'Skip')]")
    if skip_buttons:
        log_step("Post-signup step detected — clicking Skip")
        skip_buttons[0].click()
        return finish_success()

    if signup_progressed(browser):
        log_step("Signup progressed past email verification")
        return finish_success()

    if browser.current_url == "https://www.tiktok.com/login/download-app":
        return finish_success()
    return "incomplete"


def finish_success() -> str:
    global emailFull, passw, signup_complete
    if is_signup_email_page(browser):
        log_step("Refusing to save account — still on email signup page.")
        return "incomplete"
    log_step(
        "Account created — saving to users.txt, deleting verification mail"
    )
    run_log_mod.append_run(
        email_alias=emailFull,
        status="success",
        detail="signup_complete",
        url=_safe_url(browser),
        browser=browser_label,
    )
    successReg(emailFull, passw)
    import gmailReader as gr

    gr.delete_tiktok_verification_mail(
        recipient=emailFull, since_epoch=send_code_epoch
    )
    signup_complete = True
    if auto_restart and sys.platform == "win32" and "--batch" not in sys.argv:
        browser.quit()
        os.startfile(__file__)
        sys.exit(0)
    return "success"


def successReg(email: str, password: str) -> None:
    with open("users.txt", "a", encoding="utf8") as veri:
        veri.write(f"{email}:{password}\n")


def run_signup_flow(driver, alias_email: str, password: str) -> str:
    global browser, emailFull, passw, send_code_epoch, resend_count, signup_complete
    browser = driver
    emailFull = alias_email
    passw = password
    send_code_epoch = None
    resend_count = 0
    signup_complete = False

    driver.get(SIGNUP_URL)
    time.sleep(2)
    try_dismiss_cookies(driver)

    log_step("Filling birthday")
    fill_birthday(driver)
    log_step(f"Filling email: {alias_email}")
    fill_email(driver, alias_email)
    log_step("Filling password")
    fill_password(driver, password)
    log_step("Accepting email consent (optional)")
    accept_email_consent(driver)
    log_step("Clicking send code")
    send_status = click_send_code(driver)
    if send_status in ("captcha", "rate_limited", "send_code_failed"):
        return send_status
    result = getGmail()
    return result or "incomplete"


def run_one_signup() -> str:
    """Single signup attempt; returns status string for batch runner."""
    global auto_restart, signup_complete
    auto_restart = "--no-restart" not in sys.argv and "--batch" not in sys.argv
    signup_complete = False

    jsondata = load_settings()
    email_end = jsondata["eMailEnd"]
    password = jsondata["password"]
    email_user = jsondata["email"]
    alias_email = f"{email_user}+{random.randint(1, 99999)}@{email_end}"
    max_per_day = jsondata.get("maxAccountsPerDay")

    check = policy_mod.preflight(
        dry_run=False,
        argv=sys.argv,
        max_accounts_per_day=max_per_day,
        accounts_created_today=run_log_mod.count_successes_today(),
    )
    if not check.ok:
        print(check.message, flush=True)
        return "policy_blocked"

    run_log_mod.append_run(
        email_alias=alias_email,
        status="started",
        detail="signup_flow",
        browser=_browser_mode(jsondata),
    )
    driver = None
    try:
        driver = create_browser(jsondata)
        return run_signup_flow(driver, alias_email, password)
    except Exception as exc:
        run_log_mod.append_run(
            email_alias=alias_email,
            status="error",
            detail=str(exc)[:500],
            url=_safe_url(driver),
            browser=browser_label,
        )
        if driver is not None:
            save_debug_screenshot(driver, "error")
        return "error"
    finally:
        if not (signup_complete and auto_restart):
            try:
                driver.quit()
            except Exception:
                pass


def probe_signup_page(driver: webdriver.Firefox) -> dict:
    return {
        "month_combobox": bool(
            driver.find_elements(
                By.CSS_SELECTOR, '[role="combobox"][aria-label^="Month"]'
            )
        ),
        "day_combobox": bool(
            driver.find_elements(
                By.CSS_SELECTOR, '[role="combobox"][aria-label^="Day"]'
            )
        ),
        "year_combobox": bool(
            driver.find_elements(
                By.CSS_SELECTOR, '[role="combobox"][aria-label^="Year"]'
            )
        ),
        "email_field": bool(driver.find_elements(By.CSS_SELECTOR, 'input[name="email"]')),
        "password_field": bool(
            driver.find_elements(
                By.CSS_SELECTOR,
                'input[type="password"][autocomplete="new-password"]',
            )
        ),
        "send_code_button": bool(
            driver.find_elements(By.CSS_SELECTOR, 'button[data-e2e="send-code-button"]')
        ),
        "otp_field": bool(
            driver.find_elements(
                By.CSS_SELECTOR, 'input[placeholder="Enter 6-digit code"]'
            )
        ),
        "email_consent": bool(
            driver.find_elements(By.CSS_SELECTOR, "#email-consent")
        ),
    }


def dry_run() -> dict:
    """Open Firefox, load TikTok signup page, quit. No form submit or IMAP."""
    started = time.perf_counter()
    jsondata = load_settings()
    driver = create_browser(jsondata)
    try:
        driver.get(SIGNUP_URL)
        time.sleep(2)
        try_dismiss_cookies(driver)
        fields = probe_signup_page(driver)
        return {
            "ok": True,
            "title": driver.title,
            "url": driver.current_url,
            **fields,
            "seconds": round(time.perf_counter() - started, 2),
        }
    finally:
        driver.quit()


def main(dry_run_mode: bool = False) -> None:
    if dry_run_mode:
        check = policy_mod.preflight(dry_run=True, argv=sys.argv)
        if not check.ok:
            print(check.message, flush=True)
            raise SystemExit(2)
        result = dry_run()
        print("DRY RUN:", json.dumps(result, indent=2))
        return

    result = run_one_signup()
    print(f"Signup result: {result}", flush=True)
    if result != "success" and not (result == "success" and auto_restart):
        raise SystemExit(1 if result in ("error", "policy_blocked") else 2)


if __name__ == "__main__":
    main(dry_run_mode="--dry-run" in sys.argv)
