#!/usr/bin/env node
/**
 * agent-browser screenshot → POST image to bridge for every models.json alias.
 * Prerequisites: OCP :3456, bridge :3457, agent-browser on PATH.
 */
import { readFileSync, mkdirSync, existsSync } from "node:fs";
import { spawnSync } from "node:child_process";
import { dirname, join, resolve } from "node:path";
import { homedir } from "node:os";
import { fileURLToPath } from "node:url";
import { resolveAnthropicModel } from "../lib/model-map.mjs";

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = resolve(__dirname, "..");
const tmpDir = resolve(root, "tmp");
const screenshotPath = resolve(tmpDir, "all-map-models-vision.png");

const dashboardUrl =
  process.argv[2] || process.env.OCP_DASHBOARD_URL || "http://127.0.0.1:3456/dashboard";
const bridgePort = process.env.VISION_BRIDGE_PORT || "3457";
const baseUrl = `http://127.0.0.1:${bridgePort}`;
const delayMs = parseInt(process.env.MAP_TEST_DELAY_MS || "1500", 10);

function resolveAgentBrowserBin() {
  if (process.env.AGENT_BROWSER_BIN) return process.env.AGENT_BROWSER_BIN;
  const homes = [process.env.USERPROFILE, process.env.HOME, homedir()].filter(Boolean);
  const names =
    process.platform === "win32" ? ["agent-browser.cmd", "agent-browser"] : ["agent-browser"];
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
    throw new Error(`agent-browser ${args.join(" ")} failed:\n${out}`);
  }
  return out.trim();
}

function sleep(ms) {
  return new Promise((r) => setTimeout(r, ms));
}

function loadMappedModelIds() {
  const modelsPath = resolve(root, "../ocp/runtime/models.json");
  const cfg = JSON.parse(readFileSync(modelsPath, "utf8").replace(/^\uFEFF/, ""));
  const ids = new Set([
    ...Object.keys(cfg.legacyAliases || {}),
    ...Object.keys(cfg.aliases || {}),
    ...(cfg.models || []).map((m) => m.id),
  ]);
  return [...ids].sort();
}

function ensureAgentBrowser() {
  const ver = spawnSync(agentBrowser, ["--version"], {
    encoding: "utf8",
    shell: agentBrowser.endsWith(".cmd"),
  });
  if (ver.status !== 0) {
    console.error("FAIL: agent-browser not found. npm i -g agent-browser && agent-browser install");
    process.exit(1);
  }
  console.log(`agent-browser ${(ver.stdout || "").trim()}\n`);
}

async function captureScreenshot() {
  mkdirSync(tmpDir, { recursive: true });
  console.log(`[capture] ${dashboardUrl}`);
  runAgentBrowser(["open", dashboardUrl]);
  runAgentBrowser(["wait", "--load", "networkidle"]);
  const title = runAgentBrowser(["get", "title"]);
  runAgentBrowser(["screenshot", screenshotPath]);
  runAgentBrowser(["close"]);
  console.log(`[capture] saved ${screenshotPath} (title: ${title})\n`);
}

async function testModel(model, dataUrl) {
  const anthropic = resolveAnthropicModel(model);
  const body = {
    model,
    stream: false,
    messages: [
      {
        role: "user",
        content: [
          { type: "text", text: "One short sentence: what dashboard is in this image?" },
          { type: "image_url", image_url: { url: dataUrl } },
        ],
      },
    ],
  };

  const t0 = Date.now();
  const resp = await fetch(`${baseUrl}/v1/chat/completions`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: "Bearer ocp-local",
    },
    body: JSON.stringify(body),
  });
  const ms = Date.now() - t0;
  const raw = await resp.text();

  if (!resp.ok) {
    let msg = raw.slice(0, 120);
    try {
      msg = JSON.parse(raw)?.error?.message || msg;
    } catch {
      /* keep raw */
    }
    const rate = /429|529|rate|limit|quota/i.test(msg);
    return { model, anthropic, status: rate ? "RATE" : "FAIL", ms, detail: msg, fallback: false };
  }

  let content = "";
  try {
    content = JSON.parse(raw).choices?.[0]?.message?.content?.trim() || "";
  } catch {
    return { model, anthropic, status: "FAIL", ms, detail: "invalid JSON response" };
  }

  if (!content) {
    return { model, anthropic, status: "FAIL", ms, detail: "empty content", fallback: false };
  }

  const fb = resp.headers.get("x-vision-fallback") === "1";
  const status = fb ? "FB" : "PASS";
  return { model, anthropic, status, ms, detail: content.slice(0, 90), fallback: fb };
}

ensureAgentBrowser();
await captureScreenshot();

const buf = readFileSync(screenshotPath);
const dataUrl = `data:image/png;base64,${buf.toString("base64")}`;
const models = loadMappedModelIds();

console.log(`[vision] ${models.length} mapped models via ${baseUrl}\n`);
console.log("model".padEnd(22) + "anthropic".padEnd(28) + "status".padEnd(8) + "ms     snippet");
console.log("-".repeat(100));

const results = [];
for (let i = 0; i < models.length; i++) {
  const model = models[i];
  const row = await testModel(model, dataUrl);
  results.push(row);
  console.log(
    `${row.model.padEnd(22)}${row.anthropic.padEnd(28)}${row.status.padEnd(8)}${String(row.ms).padEnd(7)}${row.detail}`,
  );
  if (i < models.length - 1 && delayMs > 0) {
    await sleep(delayMs);
  }
}

const pass = results.filter((r) => r.status === "PASS").length;
const fb = results.filter((r) => r.status === "FB").length;
const rate = results.filter((r) => r.status === "RATE").length;
const fail = results.filter((r) => r.status === "FAIL").length;

console.log("\n--- summary ---");
console.log(`PASS ${pass}  FB ${fb}  RATE ${rate}  FAIL ${fail}  total ${results.length}`);
console.log("FB = Opus/Sonnet rate-limited; bridge retried with Haiku fallback");

if (pass + fb === 0) {
  console.error("\nFAIL: no model returned vision text (check bridge + claude auth)");
  process.exit(1);
}

process.exit(fail > 0 && pass + fb === 0 ? 1 : 0);
