import fs from "node:fs";
import path from "node:path";
import { markPost } from "../accounts.js";
import {
  dismissCookies,
  openWindow,
  screenshot,
} from "../browser.js";
import { isLoggedIn, runLogin } from "./login.js";

const UPLOAD_URLS = [
  "https://www.tiktok.com/tiktokstudio/upload?from=web",
  "https://www.tiktok.com/upload",
  "https://www.tiktok.com/creator-center/upload",
];

async function uploadComplete(body: string, url: string): Promise<boolean> {
  if (url.includes("/video/")) return true;
  return /uploaded|being processed|video published|manage your posts/i.test(body);
}

export async function runPost(
  email: string,
  password: string,
  videoPath: string,
  caption: string,
): Promise<string> {
  if (!fs.existsSync(videoPath)) return `error:missing_video:${videoPath}`;

  let { page, close } = await openWindow(email, "desktop");

  try {
    await page.goto(UPLOAD_URLS[0]!, { waitUntil: "domcontentloaded", timeout: 60_000 });
    await page.waitForTimeout(3000);
    await dismissCookies(page);

    if (!isLoggedIn(page)) {
      await close();
      const loginStatus = await runLogin(email, password);
      if (loginStatus !== "success") return `login_failed:${loginStatus}`;
      ({ page, close } = await openWindow(email, "desktop"));
      await page.goto(UPLOAD_URLS[0]!, { waitUntil: "domcontentloaded", timeout: 60_000 });
      await page.waitForTimeout(3000);
    }

    let fileInput = page.locator('input[type="file"]').first();
    for (let attempt = 0; attempt < 3 && !(await fileInput.count()); attempt++) {
      for (const url of UPLOAD_URLS.slice(1)) {
        await page.goto(url, { waitUntil: "domcontentloaded" });
        await page.waitForTimeout(2000);
        fileInput = page.locator('input[type="file"]').first();
        if (await fileInput.count()) break;
      }
    }

    if (!(await fileInput.count())) {
      await screenshot(page, "no-file-input");
      return "no_upload_input";
    }

    await fileInput.setInputFiles(path.resolve(videoPath));
    await page.waitForTimeout(5000);

    const editors = page.locator(
      'div[contenteditable="true"], div[role="textbox"], textarea',
    );
    if (await editors.count()) {
      await editors.first().click();
      await editors.first().fill(caption);
    }

    await page.waitForTimeout(1500);
    let posted = false;
    for (const label of ["Post", "Publish", "Upload"]) {
      const btn = page.getByRole("button", { name: label });
      if (await btn.count()) {
        await btn.first().click().catch(() => {});
        posted = true;
        break;
      }
    }
    if (!posted) {
      await screenshot(page, "no-post-button");
      return "post_button_missing";
    }

    for (let i = 0; i < 20; i++) {
      const body = await page.locator("body").innerText();
      if (await uploadComplete(body, page.url())) {
        markPost(email);
        return "success";
      }
      await page.waitForTimeout(3000);
    }

    await screenshot(page, "post-timeout");
    return "post_timeout";
  } catch (err) {
    await screenshot(page, "post-error");
    return `error:${err instanceof Error ? err.message.slice(0, 120) : String(err)}`;
  } finally {
    await close();
  }
}
