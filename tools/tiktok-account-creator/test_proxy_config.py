"""Tests for proxy_config."""

from __future__ import annotations

from proxy_config import ProxySettings, load_proxy


def test_load_proxy_from_block():
    settings = {
        "proxy": {
            "type": "socks5",
            "host": "gate.example.com",
            "port": 1080,
            "username": "user",
            "password": "secret",
        }
    }
    proxy = load_proxy(settings)
    assert proxy is not None
    assert proxy.scheme == "socks5"
    assert "socks5://user:secret@gate.example.com:1080" in (proxy.chrome_argument() or "")


def test_load_proxy_flat_keys():
    settings = {
        "proxyHost": "1.2.3.4",
        "proxyPort": 8080,
        "proxyType": "http",
    }
    proxy = load_proxy(settings)
    assert proxy is not None
    assert proxy.chrome_argument() == "--proxy-server=http://1.2.3.4:8080"


def test_disabled_when_empty():
    assert load_proxy({}) is None
