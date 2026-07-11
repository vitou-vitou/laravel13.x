from __future__ import annotations

import subprocess
import time
from pathlib import Path

from .ldplayer import LdPlayer


class AdbBridge:
    def __init__(self, ld: LdPlayer):
        self.ld = ld
        self.adb = ld.adb
        self.serial = f"127.0.0.1:{5555 + ld.index * 2}"

    def reset_server(self) -> None:
        subprocess.run([str(self.adb), "kill-server"], capture_output=True)
        time.sleep(1)
        subprocess.run([str(self.adb), "start-server"], capture_output=True)

    def connect(self) -> bool:
        self.reset_server()
        subprocess.run([str(self.adb), "connect", self.serial], capture_output=True, text=True)
        time.sleep(1)
        return self.is_ready()

    def is_ready(self) -> bool:
        p = subprocess.run([str(self.adb), "devices"], capture_output=True, text=True)
        for line in p.stdout.splitlines():
            if self.serial in line and line.strip().endswith("device"):
                return True
        return False

    def wait_ready(self, timeout: int = 90) -> bool:
        deadline = time.time() + timeout
        while time.time() < deadline:
            if self.connect():
                return True
            self.ld.adb_shell("shell input keyevent KEYCODE_WAKEUP")
            time.sleep(2)
        return False

    def shell(self, cmd: str) -> str:
        if self.is_ready():
            p = subprocess.run(
                [str(self.adb), "-s", self.serial, "shell", *cmd.split()],
                capture_output=True,
                text=True,
            )
            return (p.stdout + p.stderr).strip()
        return self.ld.adb_shell(f"shell {cmd}")

    def package_installed(self, package: str) -> bool:
        out = self.shell(f"pm path {package}")
        return "package:" in out

    def enable_via_ui(self, project_root: Path) -> None:
        ps1 = project_root / "scripts" / "enable_adb.ps1"
        if not ps1.exists():
            return
        subprocess.run(
            [
                "powershell.exe",
                "-NoProfile",
                "-ExecutionPolicy",
                "Bypass",
                "-File",
                str(ps1),
                "-LdIndex",
                str(self.ld.index),
            ],
            check=False,
        )
        time.sleep(20)
        self.ld.reboot()
        time.sleep(15)
