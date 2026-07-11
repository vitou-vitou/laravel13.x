from __future__ import annotations

import json
import subprocess
import time
from pathlib import Path

from .settings import Settings


class LdPlayer:
    """LDPlayer ldconsole wrapper."""

    def __init__(self, settings: Settings, log=print):
        self.s = settings
        self.log = log
        self.home = self._resolve_home()
        self.ldconsole = self.home / "ldconsole.exe"
        self.adb = self.home / "adb.exe"
        self.index = settings.ldplayer_index

    def _resolve_home(self) -> Path:
        candidates = [
            Path(self.s.ldplayer_home),
            Path(__import__("os").environ.get("LDPLAYER_HOME", "")),
            Path(r"D:\LDPlayer\LDPlayer9"),
            Path(r"C:\Program Files\LDPlayer\LDPlayer9"),
        ]
        for c in candidates:
            if c and (c / "ldconsole.exe").exists():
                return c
        raise FileNotFoundError("LDPlayer not found — set ldplayerHome in settings.json")

    def run(self, *args: str) -> str:
        cmd = [str(self.ldconsole), *args]
        p = subprocess.run(cmd, capture_output=True, text=True)
        return (p.stdout + p.stderr).strip()

    def is_running(self) -> bool:
        return "running" in self.run("isrunning", "--index", str(self.index)).lower()

    def launch(self) -> None:
        if self.is_running():
            self.log(f"ldplayer: already running index={self.index}")
            return
        self.log(f"ldplayer: launching index={self.index}")
        self.run("launch", "--index", str(self.index))

    def reboot(self) -> None:
        self.log(f"ldplayer: reboot index={self.index}")
        self.run("reboot", "--index", str(self.index))

    def modify_root(self) -> None:
        self.run("modify", "--index", str(self.index), "--root", "1")

    def run_app(self, package: str | None = None) -> None:
        pkg = package or self.s.mlbb_package
        self.run("runapp", "--index", str(self.index), "--packagename", pkg)

    def kill_app(self, package: str | None = None) -> None:
        pkg = package or self.s.mlbb_package
        self.run("killapp", "--index", str(self.index), "--packagename", pkg)

    def install_app(self, package: str | None = None) -> str:
        pkg = package or self.s.mlbb_package
        return self.run("installapp", "--index", str(self.index), "--packagename", pkg)

    def list2(self) -> str:
        return self.run("list2")

    def adb_shell(self, command: str) -> str:
        return self.run("adb", "--index", str(self.index), "--command", command)

    def operaterecord(self, operations: list[dict], loop_times: int = 1) -> None:
        payload = {
            "operations": operations,
            "recordInfo": {
                "loopType": 2,
                "loopTimes": loop_times,
                "circleDuration": 200,
                "loopInterval": 0,
                "loopDuration": 0,
                "accelerateTimes": 1,
                "accelerateTimesEx": 1,
            },
        }
        content = json.dumps(payload, separators=(",", ":"))
        self.run("operaterecord", "--index", str(self.index), "--content", content)

    def wait_boot(self, timeout: int = 120) -> bool:
        deadline = time.time() + timeout
        while time.time() < deadline:
            if self.is_running():
                time.sleep(3)
                return True
            time.sleep(2)
        return False
