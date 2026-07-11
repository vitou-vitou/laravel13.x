import type { Page } from "playwright";
import { chromium, type BrowserContext } from "playwright";
import path from "node:path";
import { PROFILES_DIR } from "./paths.js";

const MOBILE_UA =
  "Mozilla/5.0 (Linux; Android 11; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36";

const DESKTOP_UA =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";

function profileDir(email?: string): string {
  const safe = (email ?? "default")
    .replace(/@/g, "_at_")
    .replace(/\+/g, "_plus_");
  return path.join(PROFILES_DIR, safe);
}

export type WindowSession = {
  context: BrowserContext;
  page: Page;
  close: () => Promise<void>;
};

export async function openWindow(
  email?: string,
  mode: "mobile" | "desktop" = "desktop",
): Promise<WindowSession> {
  const dir = profileDir(email);
  const context = await chromium.launchPersistentContext(dir, {
    headless: false,
    channel: "chrome",
    viewport:
      mode === "mobile"
        ? { width: 393, height: 851 }
        : { width: 1280, height: 900 },
    userAgent: mode === "mobile" ? MOBILE_UA : DESKTOP_UA,
    args: ["--disable-blink-features=AutomationControlled"],
  });
  const page = context.pages()[0] ?? (await context.newPage());
  return {
    context,
    page,
    close: async () => {
      await context.close();
    },
  };
}

export async function dismissCookies(page: Page): Promise<void> {
  for (const label of ["Decline optional cookies", "Allow all"]) {
    const btn = page.getByRole("button", { name: label });
    if (await btn.count()) {
      await btn.first().click({ timeout: 3000 }).catch(() => {});
      return;
    }
  }
}

export async function humanType(
  page: Page,
  locator: ReturnType<Page["locator"]>,
  text: string,
): Promise<void> {
  await locator.click();
  await locator.fill("");
  for (const ch of text) {
    await locator.pressSequentially(ch, { delay: 50 + Math.random() * 120 });
  }
}

export async function screenshot(page: Page, label: string): Promise<void> {
  const file = path.join(
    path.dirname(PROFILES_DIR),
    `debug-${label}-${Date.now()}.png`,
  );
  await page.screenshot({ path: file, fullPage: true }).catch(() => {});
  console.error(`screenshot: ${file}`);
}

export async function pickBirthday(page: Page): Promise<void> {
  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December",
  ];
  const month = months[Math.floor(Math.random() * months.length)]!;
  const day = String(Math.floor(Math.random() * 28) + 1);
  const year = String(1990 + Math.floor(Math.random() * 11));

  for (const [label, value] of [
    ["Month", month],
    ["Day", day],
    ["Year", year],
  ] as const) {
    const combo = page.locator(`[role="combobox"][aria-label^="${label}"]`);
    await combo.click();
    await page
      .locator(`[role="option"]`, { hasText: new RegExp(`^${value}$`) })
      .first()
      .click();
    await page.waitForTimeout(300);
  }
}
