#!/usr/bin/env node
/**
 * E2E: agent-browser captures a page screenshot → vision bridge describes it.
 * Prerequisites: OCP on :3456, bridge on :3457, `npm i -g agent-browser`.
 */
import { readFileSync, mkdirSync, existsSync } from "node:fs";
import { spawnSync } from "node:child_process";
import { dirname, join, resolve } from "node:path";
import { homedir } from "node:os";
import { fileURLToPath } from "node:url";

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = resolve(__dirname, "..");
const tmpDir = resolve(root, "tmp");
const screenshotPath = resolve(tmpDir, "agent-browser-vision-test.png");

const dashboardUrl =
  process.argv[2] || process.env.OCP_DASHBOARD_URL || "http://127.0.0.1:3456/dashboard";
const bridgePort = process.env.VISION_BRIDGE_PORT || "3457";
const baseUrl = `http://127.0.0.1:${bridgePort}`;
const hints = (process.env.POC_HINTS || "dashboard,ocp")
  .split(",")
  .map((s) => s.trim().toLowerCase())
  .filter(Boolean);

function resolveAgentBrowserBin() {
  if (process.env.AGENT_BROWSER_BIN) return process.env.AGENT_BROWSER_BIN;
  const homes = [
    process.env.USERPROFILE,
    process.env.HOME,
    homedir(),
  ].filter(Boolean);
  const names =
    process.platform === "win32"
      ? ["agent-browser.cmd", "agent-browser"]
      : ["agent-browser"];
  for (const home of homes) {
    for (const name of names) {
      const p = join(home, ".vite-plus", "bin", name);
      if (existsSync(p)) return p;
    }
  }
  return "agent-browser";
}

const agentBrowser = resolveAgentBrowserBin();

function runAgentBrowser(args) {
  const result = spawnSync(agentBrowser, args, {
    encoding: "utf8",
    stdio: ["ignore", "pipe", "pipe"],
    shell: agentBrowser.endsWith(".cmd"),
  });
  const out = (result.stdout || "") + (result.stderr || "");
  if (result.status !== 0) {
    console.error(`FAIL: agent-browser ${args.join(" ")}\n${out}`);
    process.exit(1);
  }
  return out.trim();
}

function ensureAgentBrowser() {
  const ver = spawnSync(agentBrowser, ["--version"], {
    encoding: "utf8",
    shell: agentBrowser.endsWith(".cmd"),
  });
  if (ver.status !== 0) {
    console.error("FAIL: agent-browser not found. Install: npm i -g agent-browser && agent-browser install");
    process.exit(1);
  }
  console.log(`agent-browser ${(ver.stdout || "").trim()}`);
}

ensureAgentBrowser();
mkdirSync(tmpDir, { recursive: true });

console.log(`[1/4] open ${dashboardUrl}`);
runAgentBrowser(["open", dashboardUrl]);
runAgentBrowser(["wait", "--load", "networkidle"]);

const title = runAgentBrowser(["get", "title"]);
console.log(`[2/4] screenshot (title: ${title})`);
runAgentBrowser(["screenshot", screenshotPath]);
runAgentBrowser(["close"]);

const buf = readFileSync(screenshotPath);
const dataUrl = `data:image/png;base64,${buf.toString("base64")}`;

const body = {
  model: "gpt-5.4-nano",
  stream: false,
  messages: [
    {
      role: "user",
      content: [
        {
          type: "text",
          text: "What web dashboard is shown? Reply in one sentence. Mention OCP if you see it.",
        },
        { type: "image_url", image_url: { url: dataUrl } },
      ],
    },
  ],
};

console.log(`[3/4] POST ${baseUrl}/v1/chat/completions`);
const resp = await fetch(`${baseUrl}/v1/chat/completions`, {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    Authorization: "Bearer ocp-local",
  },
  body: JSON.stringify(body),
});

const raw = await resp.text();
if (!resp.ok) {
  console.error("FAIL", resp.status, raw.slice(0, 500));
  process.exit(1);
}

const data = JSON.parse(raw);
const content = data.choices?.[0]?.message?.content || "";
console.log("--- response ---");
console.log(content);

if (!content.trim()) {
  console.error("FAIL: empty content");
  process.exit(1);
}

const lower = content.toLowerCase();
const missing = hints.filter((h) => !lower.includes(h));
if (missing.length > 0) {
  console.error(`FAIL: response missing hints: ${missing.join(", ")}`);
  process.exit(1);
}

console.log(`[4/4] PASS: agent-browser → bridge vision (hints: ${hints.join(", ")})`);
