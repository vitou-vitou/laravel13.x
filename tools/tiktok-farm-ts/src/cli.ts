#!/usr/bin/env node
import type { Page } from "playwright";
import { aliasEmail, loadSettings } from "./config.js";
import { latestAccount, loadAccounts } from "./accounts.js";
import { requireResearchAck } from "./policy.js";
import { SAMPLE_VIDEO } from "./paths.js";
import { openWindow, dismissCookies } from "./browser.js";
import { runSignup } from "./flows/signup.js";
import { runLogin } from "./flows/login.js";
import { runPost } from "./flows/post.js";

const argv = process.argv.slice(2);
const cmd = argv[0];

function flag(name: string): string | undefined {
  const i = argv.indexOf(name);
  return i >= 0 ? argv[i + 1] : undefined;
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
    await page.goto(
      "https://www.tiktok.com/signup/phone-or-email/email",
      { waitUntil: "domcontentloaded" },
    );
    await page.waitForTimeout(1500);
  }
}

async function probe(): Promise<number> {
  const { page, close } = await openWindow(undefined, "mobile");
  try {
    await openEmailSignup(page);
    const checks = {
      month: await page.locator('[role="combobox"][aria-label^="Month"]').count(),
      email: await page.locator('input[name="email"]').count(),
      password: await page.locator('input[type="password"]').count(),
      sendCode: await page.locator('button[data-e2e="send-code-button"]').count(),
      otp: await page.locator('input[placeholder="Enter 6-digit code"]').count(),
    };
    console.log(
      JSON.stringify(
        { ok: true, url: page.url(), title: await page.title(), ...checks },
        null,
        2,
      ),
    );
    return 0;
  } finally {
    await close();
  }
}

async function signup(): Promise<number> {
  requireResearchAck(argv);
  const settings = loadSettings();
  const email = flag("--email") ?? aliasEmail(settings);
  const status = await runSignup(settings, email);
  console.log(`signup: ${status} email=${email}`);
  return status === "success" ? 0 : 1;
}

async function login(): Promise<number> {
  const email = flag("--email");
  const password = flag("--password");
  const acc = email && password
    ? { email, password }
    : latestAccount();
  if (!acc) {
    console.error("No account — run signup first");
    return 2;
  }
  const status = await runLogin(acc.email, acc.password);
  console.log(`login: ${status} email=${acc.email}`);
  return status === "success" ? 0 : 1;
}

async function post(): Promise<number> {
  const email = flag("--email");
  const password = flag("--password");
  const acc = email && password
    ? { email, password }
    : latestAccount();
  if (!acc) {
    console.error("No account — run signup first");
    return 2;
  }
  const video = flag("--video") ?? SAMPLE_VIDEO;
  const caption = flag("--caption") ?? "farm ts test #tiktok";
  const status = await runPost(acc.email, acc.password, video, caption);
  console.log(`post: ${status} email=${acc.email}`);
  return status === "success" ? 0 : 1;
}

async function cycle(): Promise<number> {
  requireResearchAck(argv);
  const settings = loadSettings();
  const email = aliasEmail(settings);
  console.log("=== 1/3 signup ===");
  const s1 = await runSignup(settings, email);
  if (s1 !== "success") {
    console.error(`cycle stopped at signup: ${s1}`);
    return 1;
  }
  console.log("=== 2/3 login ===");
  const s2 = await runLogin(email, settings.password);
  if (s2 !== "success") {
    console.error(`cycle stopped at login: ${s2}`);
    return 1;
  }
  console.log("=== 3/3 post ===");
  const video = flag("--video") ?? SAMPLE_VIDEO;
  const caption = flag("--caption") ?? "farm ts test #tiktok";
  const s3 = await runPost(email, settings.password, video, caption);
  if (s3 !== "success") {
    console.error(`cycle stopped at post: ${s3}`);
    return 1;
  }
  console.log("cycle: success (signup + login + post)");
  return 0;
}

function accounts(): number {
  console.log(JSON.stringify(loadAccounts(), null, 2));
  return 0;
}

function usage(): never {
  console.log(`Usage:
  npm run probe
  npm run signup -- --ack-research-only
  npm run login [-- --email x --password y]
  npm run post [-- --video path --caption text]
  npm run cycle -- --ack-research-only
  npm run accounts`);
  process.exit(2);
}

async function main(): Promise<number> {
  switch (cmd) {
    case "probe":
      return probe();
    case "signup":
      return signup();
    case "login":
      return login();
    case "post":
      return post();
    case "cycle":
      return cycle();
    case "accounts":
      return accounts();
    default:
      usage();
  }
}

main().then((code) => process.exit(code));
