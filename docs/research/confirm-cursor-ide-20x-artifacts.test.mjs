/**
 * Seam A — Cursor IDE quality+speed 20x artifact readiness.
 * Public interface: research notes + skills migration layout on disk.
 * Run: node docs/research/confirm-cursor-ide-20x-artifacts.test.mjs
 */
import { existsSync, readFileSync, readdirSync } from 'node:fs'
import { join, dirname } from 'node:path'
import { fileURLToPath } from 'node:url'

const root = join(dirname(fileURLToPath(import.meta.url)), '../..')
const fails = []

function ok(cond, msg) {
  if (!cond) fails.push(msg)
}

// A1 — 20x research note
const note20 = join(root, 'docs/research/cursor-ide-quality-speed-20x.md')
ok(existsSync(note20), 'A1: missing docs/research/cursor-ide-quality-speed-20x.md')
if (existsSync(note20)) {
  const t = readFileSync(note20, 'utf8')
  ok(/## Verdict/.test(t), 'A1: 20x note missing ## Verdict')
  ok(/## Sources/.test(t), 'A1: 20x note missing ## Sources')
  ok(/cursor-ide-quality-speed-10x\.md/.test(t), 'A1: 20x note must link 10x note')
}

// A2 — 10x baseline still present
const note10 = join(root, 'docs/research/cursor-ide-quality-speed-10x.md')
ok(existsSync(note10), 'A2: missing docs/research/cursor-ide-quality-speed-10x.md')

// A3 — skills migration green
const commandsDir = join(root, '.cursor/commands')
const cmdLeft = existsSync(commandsDir)
  ? readdirSync(commandsDir).filter((f) => f.endsWith('.md'))
  : []
ok(cmdLeft.length === 0, `A3: .cursor/commands still has ${cmdLeft.join(', ') || '(unexpected)'}`)

const migratedSkills = [
  'session-handoff',
  '19-delegation-workflows',
  'super-spec',
  '7pj',
  'opsx-explore',
  'tbench-prompt-library',
  'warp-terminal',
  'selfhost-proxy-stability',
]
for (const name of migratedSkills) {
  ok(
    existsSync(join(root, `.cursor/skills/${name}/SKILL.md`)),
    `A3: missing skill .cursor/skills/${name}/SKILL.md`,
  )
}

const conflictMdc = [
  'tbench-prompt-library.mdc',
  'warp-terminal.mdc',
  'selfhost-proxy-stability.mdc',
]
for (const f of conflictMdc) {
  ok(!existsSync(join(root, `.cursor/rules/${f}`)), `A3: conflict rule still present .cursor/rules/${f}`)
}

// A4 — always-apply thin set still rules
const always = [
  '00-spec-first-superpowers.mdc',
  '03-fast-mode.mdc',
  '04-simple-code-voice.mdc',
  '99-god-speed-session.mdc',
  'caveman-mode.mdc',
  'ponytail.mdc',
  'commit-humanizer.mdc',
]
for (const f of always) {
  ok(existsSync(join(root, `.cursor/rules/${f}`)), `A4: missing always rule .cursor/rules/${f}`)
}

if (fails.length) {
  console.error('FAIL — Cursor IDE 20x artifact confirm (seam A)')
  for (const f of fails) console.error(' -', f)
  console.error('\nScope: artifact readiness only. Process checklist (seam B) not asserted.')
  process.exit(1)
}

console.log('PASS — Cursor IDE 20x artifact confirm (seam A: A1–A4)')
console.log('Note: full 20x OS (Plan/Projects/parallel/Router) is process — not covered here.')
process.exit(0)
