## Why

Storefront is clean but sparse compared to Taobao-style discovery: few filters, desktop-first grid, no sticky cart affordance.

## What Changes

- Catalog **filters** (category, price range, sort: newest/price)
- **Mobile** layout pass: 2-col grid, sticky bottom cart bar, touch targets
- **Home** sections: featured categories, “recently viewed” (session)
- Optional: reference **Taobao patterns** (dense cards, sale badges) per `docs/DESIGN.md` — no brand clone

## Non-goals

- Personalization ML
- Livestream / flash sale engine
- Separate mobile app

## Depends on

- Archive `seller-experience-v1` (more catalog inventory from vendors)
