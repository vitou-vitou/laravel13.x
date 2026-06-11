import { readFileSync } from "node:fs";
import { fileURLToPath } from "node:url";
import { dirname, join } from "node:path";

const __dirname = dirname(fileURLToPath(import.meta.url));
const modelsPath = join(__dirname, "../../ocp/runtime/models.json");

let cached;
function loadModels() {
  if (!cached) {
    const raw = readFileSync(modelsPath, "utf8").replace(/^\uFEFF/, "");
    cached = JSON.parse(raw);
  }
  return cached;
}

export function resolveAnthropicModel(openAiModel) {
  const cfg = loadModels();
  const key = String(openAiModel || "").toLowerCase();
  if (cfg.legacyAliases?.[key]) {
    return cfg.legacyAliases[key];
  }
  if (cfg.aliases?.[key]) {
    return cfg.aliases[key];
  }
  const ids = new Set(cfg.models.map((m) => m.id));
  if (ids.has(openAiModel)) {
    return openAiModel;
  }
  return process.env.OCP_VISION_MODEL || cfg.aliases.sonnet;
}

/** Haiku (or override) used when Opus/Sonnet vision hits rate limits. */
export function getVisionFallbackModel() {
  const cfg = loadModels();
  return (
    process.env.OCP_VISION_FALLBACK_MODEL ||
    process.env.OCP_VISION_MODEL ||
    cfg.aliases.haiku
  );
}
