import type { Page } from "playwright";
import { markLogin } from "../accounts.js";
import {
  dismissCookies,
  humanType,
  openWindow,
  screenshot,
} from "../browser.js";

const LOGIN_URL = "https://www.tiktok.com/login/phone-or-email/email";

const LOGGED_IN = ["/foryou", "/following", "/upload", "tiktok.com/@"];

export function isLoggedIn(page: Page): boolean {
  const url = page.url().toLowerCase();
  if (LOGGED_IN.some((m) => url.includes(m))) return true;
  if (url.includes("/login")) return false;
  return false;
}

export async function runLogin(
  email: string,
  password: string,
): Promise<string> {
  const { page, close } = await openWindow(email, "desktop");

  try {
    await page.goto(LOGIN_URL, { waitUntil: "domcontentloaded", timeout: 60_000 });
    await page.waitForTimeout(2000);
    await dismissCookies(page);

    for (const label of ["Use phone or email", "Log in with email"]) {
      const link = page.getByText(label, { exact: false });
      if (await link.count()) {
        await link.first().click().catch(() => {});
        await page.waitForTimeout(1000);
      }
    }

    const emailInput = page.locator(
      'input[name="username"], input[placeholder*="Email" i]',
    );
    await emailInput.first().waitFor({ state: "visible", timeout: 20_000 });
    await humanType(page, emailInput.first(), email);

    const passInput = page.locator(
      'input[type="password"][autocomplete="current-password"], input[type="password"]',
    );
    await humanType(page, passInput.first(), password);

    const loginBtn = page.locator(
      'button[type="submit"], button[data-e2e="login-button"]',
    );
    await loginBtn.first().click();
    await page.waitForTimeout(5000);

    if (isLoggedIn(page)) {
      markLogin(email);
      return "success";
    }

    const body = await page.locator("body").innerText();
    if (/verify|verification/i.test(body)) return "verification_required";
    if (/incorrect|wrong password/i.test(body)) return "bad_credentials";

    await screenshot(page, "login-incomplete");
    return "incomplete";
  } catch (err) {
    await screenshot(page, "login-error");
    return `error:${err instanceof Error ? err.message.slice(0, 120) : String(err)}`;
  } finally {
    await close();
  }
}
