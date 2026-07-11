"""Per-browser proxy settings (research tooling — not system-wide VPN)."""

from __future__ import annotations

from dataclasses import dataclass


@dataclass(frozen=True)
class ProxySettings:
    scheme: str  # http | socks5
    host: str
    port: int
    username: str | None = None
    password: str | None = None

    @property
    def enabled(self) -> bool:
        return bool(self.host and self.port)

    def chrome_argument(self) -> str | None:
        if not self.enabled:
            return None
        scheme = self.scheme.lower()
        if scheme not in ("http", "socks5"):
            scheme = "http"
        if self.username and self.password:
            return f"--proxy-server={scheme}://{self.username}:{self.password}@{self.host}:{self.port}"
        return f"--proxy-server={scheme}://{self.host}:{self.port}"

    def firefox_capability(self) -> dict:
        if not self.enabled:
            return {}
        scheme = self.scheme.lower()
        proxy_type = "SOCKS" if scheme == "socks5" else "MANUAL"
        payload: dict = {
            "proxyType": proxy_type,
            "socksHost" if scheme == "socks5" else "httpProxy": self.host,
            "socksPort" if scheme == "socks5" else "httpPort": self.port,
        }
        if self.username:
            payload["socksUsername" if scheme == "socks5" else "httpUsername"] = self.username
        if self.password:
            payload["socksPassword" if scheme == "socks5" else "httpPassword"] = self.password
        return payload


def load_proxy(settings: dict) -> ProxySettings | None:
    """Read proxy block from settings.json. Prefer mobile/residential proxies over VPN."""
    block = settings.get("proxy")
    if isinstance(block, dict):
        host = (block.get("host") or "").strip()
        port = block.get("port")
        if host and port:
            return ProxySettings(
                scheme=(block.get("type") or "http").strip().lower(),
                host=host,
                port=int(port),
                username=(block.get("username") or None) or None,
                password=(block.get("password") or None) or None,
            )
    host = (settings.get("proxyHost") or "").strip()
    port = settings.get("proxyPort")
    if host and port:
        return ProxySettings(
            scheme=(settings.get("proxyType") or "http").strip().lower(),
            host=host,
            port=int(port),
            username=(settings.get("proxyUser") or None),
            password=(settings.get("proxyPass") or None),
        )
    return None
