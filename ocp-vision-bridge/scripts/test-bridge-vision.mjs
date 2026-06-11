#!/usr/bin/env node
/** Integration test: POST image to local vision bridge. */
import { readFileSync } from "node:fs";
import { resolve } from "node:path";

const port = process.env.VISION_BRIDGE_PORT || "3457";
const imagePath = process.argv[2] || resolve("../ocp/runtime/docs/images/dashboard.png");
const baseUrl = `http://127.0.0.1:${port}`;

const buf = readFileSync(imagePath);
const b64 = buf.toString("base64");
const dataUrl = `data:image/png;base64,${b64}`;

const body = {
  model: "gpt-5.4-nano",
  stream: false,
  messages: [
    {
      role: "user",
      content: [
        { type: "text", text: "What product dashboard is this? One sentence." },
        { type: "image_url", image_url: { url: dataUrl } },
      ],
    },
  ],
};

console.log(`POST ${baseUrl}/v1/chat/completions`);
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
console.log("\nPASS: bridge returned vision description");
