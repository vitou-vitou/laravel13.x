import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export const TOOL_ROOT = path.resolve(__dirname, "..");
export const REPO_ROOT = path.resolve(TOOL_ROOT, "../..");
export const PROFILES_DIR = path.join(TOOL_ROOT, ".profiles");
export const ACCOUNTS_FILE = path.join(TOOL_ROOT, "accounts.json");
export const SAMPLE_VIDEO = path.join(TOOL_ROOT, "assets", "sample.mp4");
export const SETTINGS_FILE = path.join(TOOL_ROOT, "settings.json");
export const GMAIL_CODE_BIN = path.join(REPO_ROOT, "bin", "gmail-tiktok-code");
