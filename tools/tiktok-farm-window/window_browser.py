"""Visible browser with persistent profile per account."""

from __future__ import annotations

import sys
import tempfile
from pathlib import Path

from paths import CREATOR_ROOT, PROFILES_DIR

if str(CREATOR_ROOT) not in sys.path:
    sys.path.insert(0, str(CREATOR_ROOT))

import bot as creator_bot  # noqa: E402
from proxy_config import load_proxy  # noqa: E402
from selenium import webdriver  # noqa: E402
from selenium.webdriver.chrome.options import Options as ChromeOptions  # noqa: E402
from selenium.webdriver.chrome.service import Service as ChromeService  # noqa: E402
from selenium.webdriver.firefox.options import Options as FirefoxOptions  # noqa: E402
from selenium.webdriver.firefox.service import Service as FirefoxService  # noqa: E402

DESKTOP_UA = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
)


def profile_dir_for(email: str) -> Path:
    safe = email.replace("@", "_at_").replace("+", "_plus_")
    path = PROFILES_DIR / safe
    path.mkdir(parents=True, exist_ok=True)
    return path


def _apply_proxy_firefox(options: FirefoxOptions, settings: dict) -> None:
    proxy = load_proxy(settings)
    if not proxy or not proxy.enabled:
        return
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


def _apply_proxy_chrome(options: ChromeOptions, settings: dict) -> None:
    proxy = load_proxy(settings)
    if proxy and proxy.chrome_argument():
        options.add_argument(proxy.chrome_argument())


def create_window_browser(settings: dict, *, email: str | None = None, mobile: bool = False):
    """Open a visible browser. Reuses creator strategies when mobile=True."""
    if mobile:
        return creator_bot.create_browser(settings)

    mode = (settings.get("browser") or "firefox").strip().lower()
    profile = profile_dir_for(email) if email else Path(tempfile.mkdtemp(prefix="tiktok_win_"))

    if mode in ("chrome", "chrome_mobile", "chrome_uc"):
        try:
            import undetected_chromedriver as uc  # type: ignore
        except ImportError:
            uc = None

        if mode == "chrome_uc" and uc is not None:
            options = uc.ChromeOptions()
            options.add_argument(f"--user-agent={DESKTOP_UA}")
            options.add_argument("--window-size=1280,900")
            options.add_argument("--disable-blink-features=AutomationControlled")
            options.add_argument(f"--user-data-dir={profile}")
            _apply_proxy_chrome(options, settings)
            driver = uc.Chrome(options=options, use_subprocess=True)
            driver.implicitly_wait(8)
            return driver

        options = ChromeOptions()
        options.add_argument(f"--user-agent={DESKTOP_UA}")
        options.add_argument("--window-size=1280,900")
        options.add_argument("--disable-blink-features=AutomationControlled")
        options.add_experimental_option("excludeSwitches", ["enable-automation"])
        options.add_experimental_option("useAutomationExtension", False)
        options.add_argument(f"--user-data-dir={profile}")
        _apply_proxy_chrome(options, settings)
        chrome_path = settings.get("chromePath")
        if chrome_path:
            driver = webdriver.Chrome(
                service=ChromeService(executable_path=chrome_path), options=options
            )
        else:
            driver = webdriver.Chrome(options=options)
        driver.implicitly_wait(8)
        return driver

    options = FirefoxOptions()
    options.add_argument("-profile")
    options.add_argument(str(profile))
    _apply_proxy_firefox(options, settings)
    service = FirefoxService(executable_path=settings["geckoPath"])
    driver = webdriver.Firefox(service=service, options=options)
    driver.set_window_size(1280, 900)
    driver.implicitly_wait(8)
    return driver
