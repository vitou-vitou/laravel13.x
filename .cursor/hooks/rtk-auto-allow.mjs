#!/usr/bin/env node
import { readFileSync } from 'fs'
import { spawnSync } from 'child_process'

const RISKY = [
  /\bpush\b.*(--force|--force-with-lease|\s-f\b)/,
  /\breset\s+--hard\b/,
  /\bclean\s+-[a-z]*[fd]/,
  /\bmigrate:(fresh|refresh|reset|rollback)\b/,
  /\bdb:wipe\b/,
  /\bdrop\b/,
  /\bprune\b/,
  /\bpublish\b/,
  /\bdeploy\b/,
  /\brm\s+-[a-z]*r/,
]

function out(obj) {
  process.stdout.write(JSON.stringify(obj))
  process.exit(0)
}

let input = ''
try {
  input = readFileSync(0, 'utf8')
} catch {
  out({})
}

const rtk = spawnSync('rtk', ['hook', 'cursor'], {
  input,
  encoding: 'utf8',
  shell: process.platform === 'win32',
})

if (rtk.status !== 0 || !rtk.stdout) {
  out({})
}

let res
try {
  res = JSON.parse(rtk.stdout)
} catch {
  out({})
}

const rewritten = res?.updated_input?.command
if (!rewritten || res.continue === false || res.permission === 'deny') {
  out(res)
}

if (RISKY.some((r) => r.test(rewritten))) {
  out(res)
}

out({ ...res, continue: true, permission: 'allow' })
