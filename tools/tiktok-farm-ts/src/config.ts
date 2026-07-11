import fs from "node:fs";
import { SETTINGS_FILE } from "./paths.js";

export type Settings = {
  email: string;
  eMailEnd: string;
  gmailPass: string;
  password: string;
  maxAccountsPerDay?: number;
};

export function loadSettings(): Settings {
  if (!fs.existsSync(SETTINGS_FILE)) {
    throw new Error(
      `Missing ${SETTINGS_FILE}. Copy settings.example.json → settings.json`,
    );
  }
  const raw = JSON.parse(fs.readFileSync(SETTINGS_FILE, "utf8")) as Settings;
  for (const key of ["email", "eMailEnd", "gmailPass", "password"] as const) {
    if (!raw[key]) throw new Error(`settings.json missing "${key}"`);
  }
  return raw;
}

export function aliasEmail(settings: Settings): string {
  const n = Math.floor(Math.random() * 99999) + 1;
  return `${settings.email}+${n}@${settings.eMailEnd}`;
}
