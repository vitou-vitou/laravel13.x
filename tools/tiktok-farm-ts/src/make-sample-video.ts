import fs from "node:fs";
import { execFile } from "node:child_process";
import { promisify } from "node:util";
import { SAMPLE_VIDEO } from "./paths.js";

const execFileAsync = promisify(execFile);

async function main(): Promise<void> {
  fs.mkdirSync(SAMPLE_VIDEO.replace(/[/\\][^/\\]+$/, ""), { recursive: true });
  if (fs.existsSync(SAMPLE_VIDEO)) {
    console.log(`exists: ${SAMPLE_VIDEO}`);
    return;
  }

  const sibling = SAMPLE_VIDEO.replace(
    "tiktok-farm-ts",
    "tiktok-farm-window",
  );
  if (fs.existsSync(sibling)) {
    fs.copyFileSync(sibling, SAMPLE_VIDEO);
    console.log(`copied: ${sibling} → ${SAMPLE_VIDEO}`);
    return;
  }

  const ffmpeg = process.platform === "win32" ? "ffmpeg.exe" : "ffmpeg";
  await execFileAsync(
    ffmpeg,
    [
      "-y",
      "-f", "lavfi", "-i", "color=c=green:s=720x1280:d=3",
      "-f", "lavfi", "-i", "sine=frequency=440:duration=3",
      "-c:v", "libx264", "-pix_fmt", "yuv420p",
      "-c:a", "aac", "-shortest",
      SAMPLE_VIDEO,
    ],
    { windowsHide: true },
  );
  console.log(`wrote: ${SAMPLE_VIDEO}`);
}

main().catch((err) => {
  console.error("Install ffmpeg or copy a .mp4 to assets/sample.mp4", err);
  process.exit(1);
});
