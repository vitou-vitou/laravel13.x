/**
 * Record kindly-pgi-da-v1 mobile walkthrough with real Playwright device profile
 * (UA, touch, deviceScaleFactor) — not viewport-only resize.
 *
 * Usage (from examples/kindly-pgi-da-v1):
 *   npm install
 *   npm run record:mobile:install
 *   npm run record:mobile -- "Pixel 7"
 */
import { chromium, devices } from 'playwright';
import { copyFile, mkdir, readdir, unlink } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const videosDir = path.join(root, 'docs', 'videos');
const deviceName = process.argv[2] ?? 'Pixel 7';
const device = devices[deviceName];

if (!device) {
  console.error(`Unknown device "${deviceName}". Try: Pixel 7, iPhone 14`);
  process.exit(1);
}

const slug = deviceName.toLowerCase().replace(/\s+/g, '-');
const outFile = path.join(videosDir, `kindly-pgi-da-v1-home-${slug}-native.webm`);
const url = process.env.APP_URL ?? 'http://kindly-pgi-da-v1.test';

await mkdir(videosDir, { recursive: true });

const browser = await chromium.launch();
const context = await browser.newContext({
  ...device,
  // Omit recordVideo.size — video uses viewport × deviceScaleFactor (native phone pixels).
  recordVideo: { dir: videosDir },
});
const page = await context.newPage();

await page.goto(url, { waitUntil: 'domcontentloaded' });
await page.getByText('For you').first().waitFor({ state: 'visible', timeout: 30_000 });

// Optional status bar — reads like a phone screen recording, not a bare browser viewport.
if (process.env.MOBILE_STATUS_BAR !== '0') {
  await page.evaluate(() => {
    const bar = document.createElement('div');
    bar.setAttribute('aria-hidden', 'true');
    bar.style.cssText =
      'position:fixed;top:0;left:0;right:0;height:28px;background:rgba(0,0,0,0.92);color:#f5f5f5;font:500 11px/28px system-ui,-apple-system,sans-serif;display:flex;align-items:center;justify-content:space-between;padding:0 14px;z-index:2147483647;pointer-events:none;letter-spacing:0.02em;';
    bar.innerHTML = '<span>9:41</span><span style="opacity:0.9">5G &nbsp; ▮▮▮▮ &nbsp; 🔋</span>';
    document.documentElement.appendChild(bar);
  });
}

await page.evaluate(async () => {
  const delay = (ms) => new Promise((r) => setTimeout(r, ms));
  for (let y = 0; y < 2400; y += 400) {
    window.scrollTo({ top: y, behavior: 'smooth' });
    await delay(650);
  }
});
await page.waitForTimeout(600);

const video = page.video();
await context.close();
await browser.close();

if (!video) {
  console.error('No video attachment on page.');
  process.exit(1);
}

const recorded = await video.path();
await copyFile(recorded, outFile);

// Playwright leaves a random page@*.webm in docs/videos — remove after copy.
for (const name of await readdir(videosDir)) {
  if (name.startsWith('page@') && name.endsWith('.webm')) {
    await unlink(path.join(videosDir, name));
  }
}

console.log(`Saved ${outFile}`);
console.log(
  `Device: ${deviceName} | viewport ${device.viewport.width}×${device.viewport.height} | DPR ${device.deviceScaleFactor} | ~${Math.round(device.viewport.width * device.deviceScaleFactor)}×${Math.round(device.viewport.height * device.deviceScaleFactor)} px video`,
);
