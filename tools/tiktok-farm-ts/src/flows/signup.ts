import type { Page } from "playwright";
import type { Settings } from "../config.js";
import { upsertAccount } from "../accounts.js";
import {
  dismissCookies,
  humanType,
  openWindow,
  pickBirthday,
  screenshot,
} from "../browser.js";
import { fetchOtp } from "../gmail.js";

const SIGNUP_URL = "https://www.tiktok.com/signup/phone-or-email/email";

const SUCCESS_MARKERS = [
  "/signup/create-username",
  "/foryou",
  "/login/download-app",
  "islands/tiktok_web",
];

function onSignupEmailPage(url: string): boolean {
  return url.includes("/signup/phone-or-email/email");
}

function signupProgressed(url: string): boolean {
  if (onSignupEmailPage(url)) return false;
  return SUCCESS_MARKERS.some((m) => url.includes(m));
}

async function handleUsername(page: Page): Promise<void> {
  if (!page.url().includes("/signup/create-username")) return;
  const field = page.locator(
    'input[name="username"], input[placeholder*="username" i]',
  );
  if (await field.count()) {
    const name = `user_${Math.random().toString(36).slice(2, 10)}`;
    await humanType(page, field.first(), name);
  }
  for (const label of ["Next", "Continue", "Sign up", "Skip"]) {
    const btn = page.getByRole("button", { name: label });
    if (await btn.count()) {
      await btn.first().click().catch(() => {});
      await page.waitForTimeout(1500);
      return;
    }
  }
}

async function openEmailSignup(page: Page): Promise<void> {
  await page.goto("https://www.tiktok.com/signup", {
    waitUntil: "domcontentloaded",
    timeout: 60_000,
  });
  await page.waitForTimeout(2000);
  await dismissCookies(page);

  for (const label of ["Use phone or email", "Sign up with email"]) {
    const el = page.getByText(label, { exact: false });
    if (await el.count()) {
      await el.first().click().catch(() => {});
      await page.waitForTimeout(800);
    }
  }

  if (!page.url().includes("/email")) {
    await page.goto(SIGNUP_URL, { waitUntil: "domcontentloaded" });
    await page.waitForTimeout(1500);
  }
}

export async function runSignup(
  settings: Settings,
  email: string,
): Promise<string> {
  const session = await openWindow(email, "desktop");
  const { page, close } = session;
  const password = settings.password;
  let sendEpoch = 0;

  try {
    await openEmailSignup(page);

    const monthCombo = page.locator('[role="combobox"][aria-label^="Month"]');
    if (await monthCombo.count()) {
      await pickBirthday(page);
    } else {
      await page.waitForTimeout(2000);
      if (await monthCombo.count()) await pickBirthday(page);
    }
    await humanType(page, page.locator('input[name="email"]').first(), email);
    const passLoc = page.locator(
      'input[type="password"][autocomplete="new-password"], input[type="password"]',
    );
    await passLoc.first().waitFor({ state: "visible", timeout: 20_000 });
    await humanType(page, passLoc.first(), password);

    const consent = page.locator("#email-consent");
    if (await consent.count()) {
      const checked = await consent.first().isChecked().catch(() => true);
      if (!checked) await page.locator('label[for="email-consent"]').first().click();
    }

    console.log("clicking send code…");
    const sendBtn = page.locator('button[data-e2e="send-code-button"]');
    await sendBtn.click();
    sendEpoch = Date.now() / 1000;
    await page.waitForTimeout(8000);

    const otpField = page.locator('input[placeholder="Enter 6-digit code"]');
    try {
      await otpField.waitFor({ state: "visible", timeout: 45_000 });
    } catch {
      await screenshot(page, "send-code-failed");
      return "send_code_failed";
    }

    let code: string | null = null;
    for (let i = 0; i < 25; i++) {
      console.log(`otp poll ${i + 1}/25…`);
      try {
        code = await fetchOtp(settings, email, sendEpoch);
      } catch {
        code = null;
      }
      if (code) break;
      await page.waitForTimeout(3000);
    }
    if (!code) {
      await screenshot(page, "otp-timeout");
      return "otp_timeout";
    }

    await humanType(page, otpField, code);
    const submit = page.locator('button[type="submit"]');
    await submit.waitFor({ state: "visible", timeout: 15_000 });
    await page.waitForTimeout(500);
    await submit.click();
    await page.waitForTimeout(3000);

    let body = await page.locator("body").innerText();
    if (/expired or incorrect|incorrect code/i.test(body)) {
      const resend = page.getByRole("button", { name: /resend/i });
      if (await resend.count()) {
        await resend.first().click();
        sendEpoch = Date.now() / 1000;
        await page.waitForTimeout(8000);
        code = null;
        for (let i = 0; i < 25; i++) {
          try {
            code = await fetchOtp(settings, email, sendEpoch);
          } catch {
            code = null;
          }
          if (code) break;
          await page.waitForTimeout(3000);
        }
        if (!code) {
          await screenshot(page, "otp-resend-timeout");
          return "otp_timeout";
        }
        await otpField.fill("");
        await humanType(page, otpField, code);
        await submit.click();
        await page.waitForTimeout(3000);
        body = await page.locator("body").innerText();
      }
    }

    if (/incorrect code|try again later|maximum number/i.test(body)) {
      await screenshot(page, "verify-fail");
      return "verification_failed";
    }

    await handleUsername(page);
    await page.waitForTimeout(2000);

    const url = page.url();
    if (signupProgressed(url)) {
      upsertAccount(email, password);
      console.log(`account saved: ${email}`);
      return "success";
    }

    await screenshot(page, "incomplete");
    return "incomplete";
  } catch (err) {
    await screenshot(page, "signup-error");
    return `error:${err instanceof Error ? err.message.slice(0, 120) : String(err)}`;
  } finally {
    await close();
  }
}
