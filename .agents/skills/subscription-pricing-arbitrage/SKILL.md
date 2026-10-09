---
name: subscription-pricing-arbitrage
description: Deep research + actionable learning path for regional App Store pricing arbitrage on AI subscriptions (Claude, Cursor, ChatGPT, GitHub Copilot, etc.) — gift card sources, redemption workflows, constraints, gotchas, and proof-of-concept documentation. Use when user asks "how do I get Cursor/Claude cheap", "where to buy gift cards", "regional arbitrage", or wants to learn the mechanism behind cheap subscription prices.
---

# Subscription Pricing Arbitrage — Learn and Explore

## When to use
User wants to **learn how** regional App Store pricing works and how to exploit it legitimately (not scam/risk), or wants to explore where to buy gift cards for cheap subscriptions. This is the "how to get it" skill; the `subscription-pricing-lookup` skill handles "what's cheapest".

## Key concepts (teach these)

### Why prices differ by country
Apple sets App Store prices per-region using **price tiers** anchored to USD, adjusted for local purchasing power + tax. Some regions (Philippines, Pakistan, Turkey, Argentina, Egypt) have significantly lower USD-equivalent prices than the US ($20) or Europe (EUR 20+). See [Galva Apple Pricing Tiers](https://galva.io/tools/apple-pricing-tiers) for the full matrix.

### The arbitrage loop
1. Buy a **regional Apple gift card** for a cheap-price country (PH, PK, AR, EG, TR)
2. Redeem it to a **secondary Apple ID** set to that country
3. Subscribe to Cursor/Claude/etc. through that Apple ID — gets the discounted regional price
4. Repeat when gift card balance runs out

### Proof sources (cite these)
- [OpenTheRank — Cursor Pricing by Country](https://opentherank.com/ai-pricing/cursor/) — official App Store regional prices
- [OpenTheRank — Claude Pricing by Country](https://opentherank.com/ai-pricing/claude/) — same for Claude
- [Galva — Apple App Store Pricing Tiers](https://galva.io/tools/apple-pricing-tiers) — Apple full tier matrix per country
- [Evertry — How to Pay for Cursor in Asia](https://evertry.co/blog/how-to-pay-for-cursor-subscription-in-asia/) — payment method guides per Asian country
- [Cursor Pricing](https://cursor.com/pricing) — official pricing page

### Constraints and gotchas (warn before user tries)

| Constraint | Detail | Risk |
|---|---|---|
| **Billing address required** | Some services (Cursor, ChatGPT) require a valid local billing address in the gift card country | Cannot fake this easily — need a real address or PO box |
| **Apple ID lock** | Redeeming too many gift cards from different countries on one Apple ID can trigger Apple account lock | Use separate Apple IDs per country |
| **VPN detection** | Some services detect VPNs and may block or flag accounts | Use residential proxies, not datacenter VPNs |
| **Gift card scams** | Some resellers sell already-redeemed codes | Buy from official Apple stores or verified resellers (OffGamers, Eneba, Raise) |
| **Account revocation** | Services may detect and terminate accounts subscribed via regional arbitrage | Low risk for legitimate gift card usage; high risk for stolen cards |
| **Tax implications** | Some regions include tax in price (Japan, EU), others add at checkout (US, Canada, Pakistan) | Price may be higher than listed |

### Region difficulty rating (for quick reference)

| Region | Price (Cursor Pro) | Difficulty | Notes |
|---|---|---|---|
| Cambodia | $25.99 | Easiest | Already cheapest for most services, no arbitrage needed |
| Japan | $25.90 | Easy | No billing address requirement, stable |
| Canada | $25.33 | Easy | Low tax, reliable |
| Pakistan | $24.85 | Medium | Needs VPN + local billing address |
| Philippines | $24.85 | Medium | GCash payment if you have PH contact |
| Egypt | $25.53 | Medium | Needs local billing address |
| Argentina | $25.99 | Hard | Very strict VPN detection, accounts get locked |
| Turkey | $30.95 | Hard | Hardest to maintain, strict detection |

### Gift card sources (verified, legitimate)

| Source | Regions | Notes |
|---|---|---|
| **Apple Store (official)** | PH, PK, JP, EG | Buy digital codes directly |
| **OffGamers.com** | Global | Regional codes, instant delivery |
| **Eneba.com** | Global | Marketplace, prices vary |
| **Raise.com** | US, CA | Discounted verified cards |
| **Amazon.co.jp** | JP | Japanese digital codes |
| **Shopee PH** | PH | Philippine digital codes |
| **Daraz.pk** | PK | Pakistan digital codes |

## Steps to answer

1. **Determine the user local price** — run `subscriptionPricing.mjs --app=<name> --cached` if available, or check OpenTheRank directly.
2. **Compare to cheapest official region** — if user local price is already near-cheapest (like Cambodia for Claude), say so — no arbitrage needed.
3. **If arbitrage makes sense**, walk through the loop:
   - Which region to target (cheapest + easiest combo)
   - Where to buy the gift card
   - How to create/redeem on a secondary Apple ID
   - What billing address to use (or if it is needed)
   - How to subscribe through that Apple ID
4. **Warn about constraints** — especially VPN detection, billing address requirements, and account lock risks.
5. **If user wants to explore further**, point to the proof sources above.

## Distinguishing from `subscription-pricing-lookup`
That skill answers "what is cheapest". This skill answers "how do I actually get it" and "teach me the mechanism". Use both together for a complete picture.

## Distinguishing from `router-free-model-bench`
That skill is for LLM API model benchmarking (speed/quality of `:free` chat models). This skill is for consumer subscription pricing (App Store, not API). Do not mix them up.
