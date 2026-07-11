from __future__ import annotations

import time
from pathlib import Path

from ..adb import AdbBridge
from ..capture import grab_frame
from ..ldplayer import LdPlayer
from ..settings import ROOT, Settings
from ..touch import smart_tap, swipe, tap
from ..vision import screen_state


def ensure_ldplayer(ld: LdPlayer) -> None:
    ld.launch()
    ld.wait_boot(90)
    time.sleep(5)
    ld.modify_root()


def ensure_adb(ld: LdPlayer, adb: AdbBridge) -> bool:
    if adb.wait_ready(20):
        return True
    print("adb: not ready — continuing with operaterecord + host capture")
    return False


def mlbb_installed_host(ld: LdPlayer, settings: Settings) -> bool:
    """Detect MLBB without ADB — launch package and read screen."""
    ld.run_app()
    time.sleep(10)
    try:
        frame = grab_frame(settings, settings.screenshot_dir / "mlbb-probe.png")
    except RuntimeError:
        return False
    st = screen_state(frame[:, :, ::-1])
    print(f"mlbb_host_probe: state={st}")
    return st not in ("emulator_home", "unknown")


def mlbb_installed(adb: AdbBridge, settings: Settings) -> bool:
    if adb.is_ready():
        return adb.package_installed(settings.mlbb_package)
    return False


def install_mlbb_ldstore(ld: LdPlayer, settings: Settings, shots: Path) -> bool:
    """Install MLBB via LDStore UI taps (no ADB required)."""
    print("install: opening LDStore")
    tap(ld, settings, 1220, 200, 1.2)
    time.sleep(5)
    try:
        grab_frame(settings, shots / "ldstore.png")
    except RuntimeError as e:
        print(f"install: capture warn — {e}")

    # Search bar top-center
    tap(ld, settings, 800, 120, 0.8)
    time.sleep(1)

    # Type via LDPlayer macro keyboard — fallback: tap known MLBB tile if visible
    # LDStore home often shows MLBB in carousel; try horizontal game icons row
    for x in (280, 420, 560, 700, 840, 980, 1120):
        tap(ld, settings, x, 780, 0.6)
        frame = grab_frame(settings, shots / f"store-tap-{x}.png")
        st = screen_state(frame[:, :, ::-1])
        if st not in ("emulator_home",):
            break

    # Install / Download button area (usually bottom or center)
    tap(ld, settings, 800, 720, 1.0)
    tap(ld, settings, 800, 650, 1.0)
    time.sleep(30)
    grab_frame(settings, shots / "install-wait.png")
    return True


def install_mlbb(ld: LdPlayer, adb: AdbBridge, settings: Settings) -> bool:
    shots = settings.screenshot_dir
    shots.mkdir(parents=True, exist_ok=True)

    if mlbb_installed(adb, settings):
        print("install: MLBB already installed")
        return True

    print("install: trying ldconsole installapp")
    ld.install_app()
    time.sleep(20)
    if mlbb_installed(adb, settings):
        print("install: installapp succeeded")
        return True

    print("install: falling back to LDStore UI")
    install_mlbb_ldstore(ld, settings, shots)
    time.sleep(60)
    if mlbb_installed(adb, settings):
        return True

    # Host-only check: try launching package
    ld.run_app()
    time.sleep(15)
    frame = grab_frame(settings, shots / "launch-probe.png")
    st = screen_state(frame[:, :, ::-1])
    print(f"install: post-launch screen={st}")
    return st in ("lobby", "loading_or_lobby", "hero_select", "in_match")


def flow_preflight(settings: Settings | None = None) -> int:
    settings = settings or Settings.load()
    settings.screenshot_dir.mkdir(parents=True, exist_ok=True)
    ld = LdPlayer(settings)
    adb = AdbBridge(ld)

    print(f"==> preflight index={ld.index} home={ld.home}")
    ensure_ldplayer(ld)

    adb_ok = ensure_adb(ld, adb)
    print(f"==> adb_ready={adb_ok}")

    if adb_ok:
        boot = adb.shell("getprop sys.boot_completed").strip()
        print(f"==> boot_completed={boot}")
        installed = mlbb_installed(adb, settings)
    else:
        print("==> adb unavailable — using host capture + operaterecord only")
        installed = mlbb_installed_host(ld, settings)

    if not installed and adb_ok:
        installed = mlbb_installed_host(ld, settings)

    print(f"==> mlbb_installed={installed}")
    if not installed:
        print("preflight: MLBB missing — run: python play.py install")
        grab_frame(settings, settings.screenshot_dir / "preflight-no-mlbb.png")
        return 2

    grab_frame(settings, settings.screenshot_dir / "preflight-ok.png")
    print("preflight: ok")
    return 0
