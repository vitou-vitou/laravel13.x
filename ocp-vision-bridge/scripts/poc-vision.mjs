#!/usr/bin/env node
/**
 * POC: prove Claude subscription OAuth can read a local image via /v1/messages.
 *
 * Usage:
 *   node scripts/poc-vision.mjs path/to/screenshot.png
 *   npm run poc -- path/to/screenshot.png
 */
import { describeImage } from "../lib/anthropic-vision.mjs";

const imagePath = process.argv[2];

if (!imagePath) {
  console.error("Usage: node scripts/poc-vision.mjs <image.png|jpg>");
  process.exit(2);
}

const hints = (process.env.POC_HINTS || "")
  .split(",")
  .map((s) => s.trim().toLowerCase())
  .filter(Boolean);

console.log("=== OCP Vision Bridge POC ===");
console.log(`Image: ${imagePath}`);
console.log(`Model: ${process.env.OCP_VISION_MODEL || "claude-haiku-4-5-20251001"}`);
console.log("");

try {
  const { text, model, usage } = await describeImage(imagePath);
  console.log("--- Model response ---");
  console.log(text);
  console.log("");
  console.log(`Model: ${model}`);
  if (usage) {
    console.log(`Tokens: in=${usage.input_tokens} out=${usage.output_tokens}`);
  }

  if (hints.length > 0) {
    const lower = text.toLowerCase();
    const missing = hints.filter((h) => !lower.includes(h));
    if (missing.length > 0) {
      console.error("");
      console.error(`POC FAIL: response missing expected hints: ${missing.join(", ")}`);
      process.exit(1);
    }
    console.log("");
    console.log(`POC PASS: response mentions hint(s): ${hints.join(", ")}`);
  } else {
    console.log("");
    console.log("POC OK: received non-empty vision response (no POC_HINTS set).");
  }
} catch (err) {
  console.error("POC FAIL:", err.message);
  process.exit(1);
}
