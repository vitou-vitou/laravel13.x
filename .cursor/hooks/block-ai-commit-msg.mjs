#!/usr/bin/env node
/**
 * Cursor beforeShellExecution: BLOCK git commits with AI / templated messages.
 * failClosed — mandatory.
 */
import { readFileSync } from 'fs'
import { spawnSync } from 'child_process'
import { fileURLToPath } from 'url'
import { dirname, resolve } from 'path'

const __dirname = dirname(fileURLToPath(import.meta.url))
const ROOT = resolve(__dirname, '../..')
const CHECKER = (() => {
  const primary = resolve(ROOT, '.githooks/check-commit-humanizer.mjs')
  const fallback = resolve(ROOT, 'scripts/check-commit-humanizer.mjs')
  try {
    readFileSync(primary)
    return primary
  } catch {
    return fallback
  }
})()

function out(obj) {
  process.stdout.write(JSON.stringify(obj))
}

let input = ''
try {
  input = readFileSync(0, 'utf8')
} catch {
  out({ permission: 'allow' })
  process.exit(0)
}

let payload = {}
try {
  payload = JSON.parse(input || '{}')
} catch {
  out({ permission: 'allow' })
  process.exit(0)
}

const command = String(payload.command || '')

if (!/\bgit\b[\s\S]*\bcommit\b/.test(command)) {
  out({ permission: 'allow' })
  process.exit(0)
}

if (/(?:^|\s)(--no-verify|-n)(?:\s|$)/.test(command)) {
  out({
    permission: 'deny',
    user_message: 'Blocked: --no-verify on git commit (AI commit-message gate is mandatory).',
    agent_message: 'Do not skip commit-msg hooks. Rewrite message to pass humanizer, then commit without --no-verify.',
  })
  process.exit(0)
}

function extractMessages(cmd) {
  const msgs = []
  const re = /(?:^|\s)-m\s+(?:"([^"]*)"|'([^']*)'|\$\((?:cat|printf)\s+<<['"]?EOF['"]?([\s\S]*?)EOF\))/g
  let m
  while ((m = re.exec(cmd)) !== null) {
    msgs.push(m[1] ?? m[2] ?? m[3] ?? '')
  }
  // HEREDOC: cat <<'EOF' ... EOF inside $(...)
  const hd = /<<['"]?EOF['"]?\s*\n([\s\S]*?)\nEOF/g
  let h
  while ((h = hd.exec(cmd)) !== null) {
    msgs.push(h[1])
  }
  return msgs.map((s) => s.trim()).filter(Boolean)
}

const messages = extractMessages(command)

// If commit has no extractable -m / HEREDOC, still scan raw command for AI trailers.
if (/Co-Authored-By:\s*.*(Cursor|Claude|Copilot|ChatGPT)/i.test(command)) {
  out({
    permission: 'deny',
    user_message: 'Blocked: AI Co-Authored-By trailer in git commit.',
    agent_message: 'Remove Co-Authored-By AI trailers. Use short Adjective Noun title only.',
  })
  process.exit(0)
}

if (!messages.length) {
  // Editor / -F path commits: allow shell; git commit-msg hook still enforces.
  out({ permission: 'allow' })
  process.exit(0)
}

for (const msg of messages) {
  const r = spawnSync(process.execPath, [CHECKER, '--stdin'], {
    input: msg,
    encoding: 'utf8',
  })
  if (r.status !== 0) {
    const detail = (r.stderr || r.stdout || '').trim()
    out({
      permission: 'deny',
      user_message: 'Blocked: commit message failed humanizer (AI / templated).',
      agent_message: `${detail}\nRewrite to short Adjective Noun title (e.g. Cleaner PI Schedule). No AI trailers. No bullet body.`,
    })
    process.exit(0)
  }
}

out({ permission: 'allow' })
process.exit(0)
