"""
Autonomous egress proxies — no user prompts.

Gold unchanged: 10 accounts. Plan: auto-rotate egress without asking for credentials.

Notes:
- ngrok / cloudflared tunnel = INBOUND (expose local). Not TikTok browser egress.
- Tor SOCKS5 + free HTTP pool = OUTBOUND IP rotation (auto-provisioned here).
- cloudflared installed → logged for WARP/tunnel ops; signup uses Tor/pool first.
"""

from __future__ import annotations

import json
import re
import socket
import subprocess
import sys
import time
import urllib.error
import urllib.request
from dataclasses import dataclass, asdict
from pathlib import Path

TOOL_DIR = Path(__file__).resolve().parent
TOR_DIR = TOOL_DIR / ".vendor" / "tor"
TORRC_PATH = TOR_DIR / "torrc"
TOR_PID_PATH = TOR_DIR / "tor.pid"
POOL_CACHE_PATH = TOOL_DIR / ".vendor" / "proxy_pool.json"
STATE_PATH = TOOL_DIR / "goal_state.json"

TOR_SOCKS_HOST = "127.0.0.1"
TOR_SOCKS_PORT = 9050
TOR_CONTROL_PORT = 9051

FREE_PROXY_URLS = (
    "https://api.proxyscrape.com/v2/?request=displayproxies&protocol=http&timeout=8000&country=all",
    "https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt",
)

TOR_EXE_CANDIDATES = (
    TOR_DIR / "tor" / "tor.exe",
    Path(r"C:\Users\vitou\OneDrive\Desktop\Tor Browser\Browser\TorBrowser\Tor\tor.exe"),
    Path.home() / "OneDrive" / "Desktop" / "Tor Browser" / "Browser" / "TorBrowser" / "Tor" / "tor.exe",
    Path(r"C:\Program Files\Tor Browser\Browser\TorBrowser\Tor\tor.exe"),
    Path(r"C:\Program Files (x86)\Tor Browser\Browser\TorBrowser\Tor\tor.exe"),
)


@dataclass(frozen=True)
class ProxyEndpoint:
    scheme: str
    host: str
    port: int
    source: str
    username: str | None = None
    password: str | None = None

    def to_settings_dict(self) -> dict:
        return {
            "type": self.scheme,
            "host": self.host,
            "port": self.port,
            "username": self.username or "",
            "password": self.password or "",
            "source": self.source,
        }


def _port_open(host: str, port: int, timeout: float = 1.5) -> bool:
    try:
        with socket.create_connection((host, port), timeout=timeout):
            return True
    except OSError:
        return False


def _find_tor_exe() -> Path | None:
    for path in TOR_EXE_CANDIDATES:
        if path.is_file():
            return path
    return None


def _write_torrc() -> None:
    TOR_DIR.mkdir(parents=True, exist_ok=True)
    TORRC_PATH.write_text(
        f"SocksPort {TOR_SOCKS_PORT}\n"
        f"ControlPort {TOR_CONTROL_PORT}\n"
        "CookieAuthentication 0\n"
        "AvoidDiskWrites 1\n"
        "Log notice stdout\n",
        encoding="utf-8",
    )


def _try_winget_tor() -> Path | None:
    for pkg in ("TorProject.TorBrowser", "TorProject.Tor"):
        try:
            proc = subprocess.run(
                [
                    "winget",
                    "install",
                    "--id",
                    pkg,
                    "-e",
                    "--accept-package-agreements",
                    "--accept-source-agreements",
                ],
                capture_output=True,
                text=True,
                timeout=600,
            )
            if proc.returncode in (0, 2316632107):
                found = _find_tor_exe()
                if found:
                    return found
        except (OSError, subprocess.TimeoutExpired):
            continue
    return _find_tor_exe()


def ensure_tor_running() -> bool:
    """Start local Tor SOCKS if possible. Returns True when port is open."""
    if _port_open(TOR_SOCKS_HOST, TOR_SOCKS_PORT):
        return True

    tor_exe = _find_tor_exe() or _try_winget_tor()
    if not tor_exe:
        return False

    _write_torrc()
    TOR_DIR.mkdir(parents=True, exist_ok=True)
    log_path = TOR_DIR / "tor.log"
    try:
        with log_path.open("a", encoding="utf-8") as logf:
            proc = subprocess.Popen(
                [str(tor_exe), "-f", str(TORRC_PATH)],
                cwd=str(TOR_DIR),
                stdout=logf,
                stderr=subprocess.STDOUT,
                creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
            )
        TOR_PID_PATH.write_text(str(proc.pid), encoding="utf-8")
    except OSError:
        return False

    for _ in range(45):
        if _port_open(TOR_SOCKS_HOST, TOR_SOCKS_PORT):
            return True
        time.sleep(1)
    return False


def tor_new_identity() -> bool:
    """Request new Tor circuit (new exit IP)."""
    if not _port_open(TOR_SOCKS_HOST, TOR_SOCKS_PORT):
        return False
    try:
        with socket.create_connection(("127.0.0.1", TOR_CONTROL_PORT), timeout=3) as sock:
            sock.sendall(b'AUTHENTICATE\r\n')
            sock.recv(256)
            sock.sendall(b'SIGNAL NEWNYM\r\n')
            sock.recv(256)
        time.sleep(5)
        return True
    except OSError:
        return False


def _fetch_url_text(url: str, timeout: int = 20) -> str:
    req = urllib.request.Request(url, headers={"User-Agent": "tiktok-account-creator/1.0"})
    with urllib.request.urlopen(req, timeout=timeout) as resp:
        return resp.read().decode("utf-8", errors="replace")


def _parse_proxy_lines(text: str) -> list[tuple[str, int]]:
    found: list[tuple[str, int]] = []
    for line in text.splitlines():
        line = line.strip()
        if not line or line.startswith("#"):
            continue
        m = re.match(r"^([\d.]+):(\d+)$", line)
        if m:
            found.append((m.group(1), int(m.group(2))))
    return found


def _test_http_proxy(host: str, port: int, timeout: int = 8) -> bool:
    try:
        proxy_handler = urllib.request.ProxyHandler(
            {"http": f"http://{host}:{port}", "https": f"http://{host}:{port}"}
        )
        opener = urllib.request.build_opener(proxy_handler)
        req = urllib.request.Request(
            "http://api.ipify.org?format=json",
            headers={"User-Agent": "tiktok-proxy-check"},
        )
        with opener.open(req, timeout=timeout) as resp:
            return resp.status == 200
    except (urllib.error.URLError, OSError, TimeoutError):
        return False


def refresh_free_pool(max_valid: int = 8) -> list[ProxyEndpoint]:
    """Scrape and validate free HTTP proxies; cache to disk."""
    candidates: list[tuple[str, int]] = []
    for url in FREE_PROXY_URLS:
        try:
            candidates.extend(_parse_proxy_lines(_fetch_url_text(url)))
        except (urllib.error.URLError, OSError, TimeoutError):
            continue
        if len(candidates) >= 80:
            break

    valid: list[ProxyEndpoint] = []
    seen: set[str] = set()
    for host, port in candidates:
        key = f"{host}:{port}"
        if key in seen:
            continue
        seen.add(key)
        if _test_http_proxy(host, port):
            valid.append(
                ProxyEndpoint("http", host, port, source="free_pool")
            )
        if len(valid) >= max_valid:
            break

    POOL_CACHE_PATH.parent.mkdir(parents=True, exist_ok=True)
    POOL_CACHE_PATH.write_text(
        json.dumps([asdict(p) for p in valid], indent=2),
        encoding="utf-8",
    )
    return valid


def load_cached_pool() -> list[ProxyEndpoint]:
    if not POOL_CACHE_PATH.is_file():
        return []
    try:
        raw = json.loads(POOL_CACHE_PATH.read_text(encoding="utf-8"))
    except (json.JSONDecodeError, OSError):
        return []
    out: list[ProxyEndpoint] = []
    for item in raw:
        if isinstance(item, dict) and item.get("host") and item.get("port"):
            out.append(
                ProxyEndpoint(
                    scheme=item.get("scheme") or item.get("type") or "http",
                    host=str(item["host"]),
                    port=int(item["port"]),
                    source=str(item.get("source") or "cache"),
                    username=item.get("username") or None,
                    password=item.get("password") or None,
                )
            )
    return out


def tor_endpoint() -> ProxyEndpoint | None:
    if ensure_tor_running():
        return ProxyEndpoint("socks5", TOR_SOCKS_HOST, TOR_SOCKS_PORT, source="tor_local")
    return None


def build_auto_chain(refresh_pool: bool = False) -> list[dict]:
    """
    Ordered egress chain — no user credentials.
    Direct first (most reliable), then Tor, then validated free pool.
    """
    chain: list[dict] = []

    chain.append(
        ProxyEndpoint("http", "", 0, source="direct").to_settings_dict()
    )

    tor = tor_endpoint()
    if tor:
        chain.append(tor.to_settings_dict())

    pool = load_cached_pool()
    if refresh_pool or len(pool) < 2:
        pool = refresh_free_pool(max_valid=6)

    for ep in pool:
        chain.append(ep.to_settings_dict())

    return chain


def pick_proxy(settings: dict, *, rotate: bool = False) -> dict | None:
    """
    Resolve proxy for this attempt. Uses manual proxyList first, else auto chain.
    """
    manual = settings.get("proxyList") or []
    if isinstance(manual, list) and manual:
        valid = [p for p in manual if isinstance(p, dict) and p.get("host") and p.get("port")]
        if valid:
            state = {}
            if STATE_PATH.is_file():
                try:
                    state = json.loads(STATE_PATH.read_text(encoding="utf-8"))
                except (json.JSONDecodeError, OSError):
                    state = {}
            idx = int(state.get("proxy_index", 0)) % len(valid)
            return dict(valid[idx])

    if settings.get("autoProxy", True) is False:
        return None

    chain = settings.get("_autoProxyChain")
    if not isinstance(chain, list) or not chain:
        chain = build_auto_chain(refresh_pool=rotate)
        settings["_autoProxyChain"] = chain

    state = {}
    if STATE_PATH.is_file():
        try:
            state = json.loads(STATE_PATH.read_text(encoding="utf-8"))
        except (json.JSONDecodeError, OSError):
            state = {}
    idx = int(state.get("proxy_index", 0)) % max(len(chain), 1)
    chosen = dict(chain[idx])

    if chosen.get("source") == "tor_local" and rotate:
        tor_new_identity()

    if not chosen.get("host") or not chosen.get("port"):
        return None
    return chosen


def diagnose() -> dict:
    """CLI-friendly status for diagnose.py proxy."""
    tor_up = _port_open(TOR_SOCKS_HOST, TOR_SOCKS_PORT)
    pool = load_cached_pool()
    cloudflared = bool(
        subprocess.run(
            ["cloudflared", "version"],
            capture_output=True,
            timeout=5,
        ).returncode
        == 0
    )
    ngrok = bool(
        subprocess.run(["ngrok", "version"], capture_output=True, timeout=5).returncode == 0
    )
    return {
        "tor_socks": f"{TOR_SOCKS_HOST}:{TOR_SOCKS_PORT}",
        "tor_running": tor_up,
        "cached_free_proxies": len(pool),
        "cloudflared_installed": cloudflared,
        "ngrok_installed": ngrok,
        "note": (
            "ngrok/cloudflared tunnel = inbound only. "
            "Signup egress uses Tor + free pool automatically."
        ),
    }


if __name__ == "__main__":
    print(json.dumps(diagnose(), indent=2))
    chain = build_auto_chain(refresh_pool="--refresh" in sys.argv)
    print(f"auto_chain ({len(chain)}):", [c.get("source") for c in chain])
