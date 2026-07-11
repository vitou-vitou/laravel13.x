"""Create a minimal MP4 for upload tests."""

from __future__ import annotations

import subprocess
import sys
from pathlib import Path

from paths import SAMPLE_VIDEO


def main() -> int:
    SAMPLE_VIDEO.parent.mkdir(parents=True, exist_ok=True)
    if SAMPLE_VIDEO.is_file():
        print(f"exists: {SAMPLE_VIDEO}")
        return 0

    ffmpeg = "ffmpeg"
    try:
        import imageio_ffmpeg  # type: ignore

        ffmpeg = imageio_ffmpeg.get_ffmpeg_exe()
    except ImportError:
        pass

    cmd = [
        ffmpeg,
        "-y",
        "-f",
        "lavfi",
        "-i",
        "color=c=blue:s=720x1280:d=3",
        "-f",
        "lavfi",
        "-i",
        "sine=frequency=440:duration=3",
        "-c:v",
        "libx264",
        "-pix_fmt",
        "yuv420p",
        "-c:a",
        "aac",
        "-shortest",
        str(SAMPLE_VIDEO),
    ]
    try:
        subprocess.run(cmd, check=True, capture_output=True)
    except FileNotFoundError:
        print("ffmpeg not found — install ffmpeg or place any .mp4 at assets/sample.mp4", file=sys.stderr)
        return 1
    except subprocess.CalledProcessError as exc:
        print(exc.stderr.decode() if exc.stderr else exc, file=sys.stderr)
        return 1
    print(f"wrote: {SAMPLE_VIDEO}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
