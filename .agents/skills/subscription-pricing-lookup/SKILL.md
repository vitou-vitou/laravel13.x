---
name: subscription-pricing-lookup
description: Look up cheapest-region pricing for AI subscription apps (ChatGPT Plus, Claude Pro, Cursor, GitHub Copilot, Gemini, etc.) via opentherank.com/ai-pricing/. Use when the user asks "cheapest region for X subscription", "where is Cursor Pro cheapest", or references opentherank.
---

# Subscription Pricing Lookup (opentherank.com)

## When to use
User asks for cheapest-country pricing on a consumer AI subscription (not an API/model) — e.g. Cursor Pro, ChatGPT Plus, Claude Pro, GitHub Copilot, ElevenLabs, etc.

## Tool
`scripts/subscriptionPricing.mjs` — scrapes `opentherank.com/ai-pricing/`, caches to `scripts/.cache/subscriptionPricing.json` (gitignored).

```bash
node scripts/subscriptionPricing.mjs --app=cursor     # one app, fresh fetch
node scripts/subscriptionPricing.mjs --cached          # all apps, from last cache (fast)
node scripts/subscriptionPricing.mjs                   # all apps, fresh fetch
```

Outputs rank, region, USD price, and local-currency price per app.

## Known limitation — read before answering
The source page only exposes **top-4 cheapest + 1 most-expensive region per app** — not a full country list. If the user asks about a specific country (e.g. Cambodia) that isn't in those 5 rows, **say it's not available in this dataset** rather than guessing or estimating. Do not fabricate a price for an unlisted region.

## Steps
1. Run the script with `--app=<name>` (substring match against the app's name).
2. If the user's target country isn't in the output, say so explicitly — don't interpolate from neighboring countries' prices.
3. Cache is reusable across the session — use `--cached` for follow-up questions about a different app without re-fetching.

## Distinguishing from `router-free-model-bench`
This skill is for **consumer app subscriptions** (App Store regional pricing, e.g. "Cursor Pro costs $X/mo in Pakistan"). It is unrelated to LLM API model selection.

If the user asks "find me the cheapest + best quality + fastest **model**" (API-level, not subscription-level), that's the `router-free-model-bench` skill instead — it benchmarks `:free`-tier chat models on OpenAI-compatible routers (unorouter, cavoti, etc.) for latency/quality, not subscription price.
