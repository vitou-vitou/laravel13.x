import fs from "node:fs";
import { ACCOUNTS_FILE } from "./paths.js";

export type Account = {
  email: string;
  password: string;
  createdAt: string;
  lastLogin?: string;
  lastPost?: string;
  posts: number;
};

export function loadAccounts(): Account[] {
  if (!fs.existsSync(ACCOUNTS_FILE)) return [];
  const raw = JSON.parse(fs.readFileSync(ACCOUNTS_FILE, "utf8"));
  const list = Array.isArray(raw) ? raw : raw.accounts ?? [];
  return list.filter((a: Account) => a?.email && a?.password);
}

export function saveAccounts(accounts: Account[]): void {
  fs.mkdirSync(ACCOUNTS_FILE.replace(/[/\\][^/\\]+$/, ""), { recursive: true });
  fs.writeFileSync(ACCOUNTS_FILE, JSON.stringify(accounts, null, 2));
}

export function upsertAccount(email: string, password: string): Account {
  const accounts = loadAccounts();
  const existing = accounts.find((a) => a.email === email);
  if (existing) {
    existing.password = password;
    saveAccounts(accounts);
    return existing;
  }
  const acc: Account = {
    email,
    password,
    createdAt: new Date().toISOString(),
    posts: 0,
  };
  accounts.push(acc);
  saveAccounts(accounts);
  return acc;
}

export function markLogin(email: string): void {
  const accounts = loadAccounts();
  const acc = accounts.find((a) => a.email === email);
  if (acc) {
    acc.lastLogin = new Date().toISOString();
    saveAccounts(accounts);
  }
}

export function markPost(email: string): void {
  const accounts = loadAccounts();
  const acc = accounts.find((a) => a.email === email);
  if (acc) {
    acc.lastPost = new Date().toISOString();
    acc.posts += 1;
    saveAccounts(accounts);
  }
}

export function latestAccount(): Account | undefined {
  const accounts = loadAccounts();
  return accounts.at(-1);
}
