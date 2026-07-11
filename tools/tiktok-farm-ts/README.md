# TikTok farm (TypeScript 100%)

Standalone browser tool for TikTok **signup**, **login**, and **post** using **Playwright** (headed Chrome window). No Python in this package.

| Command | Description |
|---------|-------------|
| `npm run probe` | Check signup page locators |
| `npm run signup -- --ack-research-only` | Email signup + OTP |
| `npm run login` | Login with saved account |
| `npm run post` | Upload `assets/sample.mp4` |
| `npm run cycle -- --ack-research-only` | signup → login → post |

## Setup

```bash
cd tools/tiktok-farm-ts
npm install
npx playwright install chrome
cp settings.example.json settings.json   # or symlink from tiktok-account-creator
npm run sample-video
```

`settings.json` fields: Gmail username (`email`), `eMailEnd`, `gmailPass` (app password), `password` (TikTok password).

OTP order: `bin/gmail-tiktok-code` (gws) → IMAP fallback (`imapflow`).

## Research boundary

Signup and cycle require `--ack-research-only` or `TIKTOK_RESEARCH_ACK=1`. See `.agents/skills/tiktok-platform-policy-boundary/SKILL.md`.

## Shell wrapper (optional)

```bash
./bin/tiktok-farm-ts cycle --ack-research-only
```

## vs Python `tiktok-farm-window`

This project is **TypeScript-only**. The earlier Python experiment (`tools/tiktok-farm-window`) is deprecated in favor of this stack.
