#!/usr/bin/env node
/**
 * Fail closed if renamed-away long helpers return.
 *
 * Usage:
 *   node scripts/check-pl-php-helper-humanizer.mjs
 *   node scripts/check-pl-php-helper-humanizer.mjs app/Helpers/helpers.php
 */
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const targets = process.argv.slice(2).length
  ? process.argv.slice(2)
  : ['app/Helpers/helpers.php', 'app/Services/PL']

const banned = [
  'plFlattenDirectBookEndorsementDetail',
  'plEnrichDirectBookClauseFields',
  'plApplyDirectBookInsurancePeriod',
  'plNormalizeDirectBookDetailObject',
  'plEnrichDirectBookCommission',
  'plEnrichDirectBookCommissionRow',
]

function walk(dir, out = []) {
  if (!fs.existsSync(dir)) return out
  const st = fs.statSync(dir)
  if (st.isFile()) {
    if (dir.endsWith('.php')) out.push(dir)
    return out
  }
  for (const name of fs.readdirSync(dir)) {
    if (name === 'vendor' || name === 'node_modules') continue
    walk(path.join(dir, name), out)
  }
  return out
}

const files = []
for (const rel of targets) {
  const abs = path.isAbsolute(rel) ? rel : path.join(root, rel)
  walk(abs, files)
}

let failed = false
for (const file of files) {
  const text = fs.readFileSync(file, 'utf8')
  const rel = path.relative(root, file).replace(/\\/g, '/')
  for (const name of banned) {
    if (text.includes(name)) {
      console.error(`BLOCKED: ${rel} still uses ${name} — use short name (05-pl-db-naming.mdc §6)`)
      failed = true
    }
  }
}

if (failed) {
  console.error('Use: plFlatEndt, plFillClauses, plApplyPeriod, plNormDetail, plFillComm, plFillCommRow, plPrepDetail')
  process.exit(1)
}

console.log('PASS: pl PHP helper humanizer')
