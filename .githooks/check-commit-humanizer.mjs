#!/usr/bin/env node
/**
 * Fail (exit 1) if commit message looks AI-generated.
 * Usage:
 *   node scripts/check-commit-humanizer.mjs <path-to-msg-file>
 *   node scripts/check-commit-humanizer.mjs --stdin
 *   echo "msg" | node scripts/check-commit-humanizer.mjs --stdin
 */

import { readFileSync } from 'fs'

const args = process.argv.slice(2)
const useStdin = args.includes('--stdin')
const fileArg = args.find((a) => a !== '--stdin' && !a.startsWith('-'))

function readMsg() {
  if (useStdin || !fileArg) {
    return readFileSync(0, 'utf8')
  }
  return readFileSync(fileArg, 'utf8')
}

function stripComments(raw) {
  return raw
    .split(/\r?\n/)
    .filter((line) => !line.startsWith('#'))
    .join('\n')
    .trim()
}

const AI_TRAILER = /^\s*Co-Authored-By:\s*.*(Cursor|Claude|Copilot|ChatGPT|OpenAI|Anthropic|Gemini|Aider|GPT-|Codeium|Tabnine)/i
const AI_PHRASE = /(generated (by|with)\s+(ai|cursor|claude|chatgpt|copilot)|made[- ]with[- ]cursor|as an ai|🤖|ai-generated|llm[- ]generated)/i
const AI_TITLE = /^(Add|Fix|Update|Refactor|Implement|Create|Remove|Delete|Enhance|Improve)\s+.+\s+and\s+(add|fix|update|refactor|implement|create|remove|delete|enhance|improve)\b/i
const VERB_CHANGELOG = /^(Add|Fix|Update|Refactor|Implement|Create|Remove|Delete|Enhance|Improve|Feat|Chore|Docs)\b[:\s]/i

const reasons = []

function check(msg) {
  if (!msg) {
    reasons.push('empty commit message')
    return
  }

  const lines = msg.split(/\r?\n/)
  const title = (lines[0] || '').trim()
  const body = lines.slice(1).join('\n').trim()

  for (const line of lines) {
    if (AI_TRAILER.test(line)) {
      reasons.push(`AI co-author trailer blocked: ${line.trim()}`)
    }
  }

  if (AI_PHRASE.test(msg)) {
    reasons.push('AI-generated phrase detected in message')
  }

  if (AI_TITLE.test(title)) {
    reasons.push('AI-templated title ("Add X and fix Y" style)')
  }

  // Prefer Adjective Noun; block common verb-first changelog one-liners
  if (VERB_CHANGELOG.test(title) && title.split(/\s+/).length <= 8) {
    reasons.push('verb-first changelog title — use Adjective Noun (e.g. "Cleaner PI Schedule")')
  }

  const bullets = body.split(/\r?\n/).filter((l) => /^\s*[-*]\s+/.test(l))
  if (bullets.length >= 2) {
    reasons.push('bullet-list commit body looks AI-templated — keep title-only unless user asked for detail')
  }

  if (body.length > 280 && /\b(this (commit|change|pr)|in this (commit|change)|we (added|fixed|updated|implemented))\b/i.test(body)) {
    reasons.push('long explanatory body reads AI-templated')
  }
}

const msg = stripComments(readMsg())
check(msg)

if (reasons.length) {
  console.error('BLOCKED: commit message failed humanizer (AI / templated).')
  for (const r of reasons) console.error(` - ${r}`)
  console.error('')
  console.error('Required: short human Adjective Noun title; no AI trailers; no bullet-list body.')
  console.error('Example: Cleaner PI Schedule')
  process.exit(1)
}

process.exit(0)
