# Example: Angkor Wat + Khmer kou sopeap niche

Slug: `angkor-khmer-cuisine`

## Intake snapshot

- **Domain:** Khmer sour soup (kou sopeap), Angkor temple facts, approachable for English TikTok
- **Platform:** TikTok (expand Instagram later)
- **Account:** manual — user creates `@…`, agent supplies bio + calendar
- **Schedule:** 03:00 `Asia/Phnom_Penh` daily
- **Media ladder:** Week 1 text → Week 2 +image → Week 3 carousel → Week 4 video scripts
- **Framework:** deferred

## Phase 1 — last30days topics

Run separately or combined plan:

1. `Khmer kou sopeap recipe TikTok`
2. `Angkor Wat facts short video`
3. `Cambodian street food 2026`

Save: `docs/domains/angkor-khmer-cuisine/angkor-khmer-cuisine-last30days-raw.md`

## Phase 2 — Pillars (decomposition)

| Pillar | Entities |
|--------|----------|
| Sour soups | kou sopeap, samlor machu, prahok |
| Angkor | Angkor Wat, Bayon, Ta Prohm |
| Ingredients | lemongrass, galangal, tamarind |
| Travel | dry season, sunrise, dress code |

## Phase 3 — Sample content hooks (from study packet)

1. "Kou sopeap is not 'just sour soup'" — text
2. "3 Angkor myths tourists still believe" — carousel
3. "Prahok: the ingredient that splits the room" — text
4. "Why sunrise at Angkor is overrated (and what to do instead)" — text
5. "5-second kou sopeap sour balance test" — video later

## Phase 4 — Calendar rows (first 3 days)

| Day | Hook | Format | Caption start |
|-----|------|--------|---------------|
| 1 | Kou sopeap vs tom yum | text | "Same sour vibe, different soul…" |
| 2 | Angkor Wat was not built in a day | text | "It took longer than your group chat…" |
| 3 | One prahok fact | text | "This ferment is Cambodia's umami bomb…" |

## Phase 5 — SDD when automating

Greenfield Laravel example in `examples/angkor-content-scheduler/` (user creates when ready):

- spec-kit: constitution → specify scheduler + publish log
- superpowers: TDD for `PublishDomainPost` job
- Schedule: `dailyAt('03:00')->timezone('Asia/Phnom_Penh')`

## Phase 10 — Sample report queries

```sql
-- When DB exists
SELECT pillar, COUNT(*) posts, SUM(saves) saves
FROM publish_log
WHERE published_at >= NOW() - INTERVAL 7 DAY
GROUP BY pillar
ORDER BY saves DESC;
```

Manual until built: export TikTok analytics CSV → paste into weekly section of `angkor-khmer-cuisine-report-queries.md`.
