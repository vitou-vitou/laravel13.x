# Admin product — 5-year roadmap (not a UI stack)

Read **with** the detected UI pack (Filament, Nova, Backpack, Blade, …). Never instead of it.

Long drafts (throwaway): `d:\laravel13.x\.scratch\blogs\11`–`15-admin-year-*.md`. Post format → skill `tech-blog-post`.

Engine topics (Eloquent, queues, mail, cache, broadcast) stay on [laravel.com/docs](https://laravel.com/docs) for the locked major. This pack is **when** to add them, not a tutorial.

## Goals

1. **Guide 5-year operational maturity**: Walk any web-app admin through Operate → Trust → Scale → Platform → Endure.
2. **Prevent premature complexity**: Block year-3 queue/caching or year-4 platform sprawl while year-1 backups and year-2 policies are unproven.
3. **Protect the single human door**: Ensure the chosen admin package remains the sole authority without parallel UI rewrites.
4. **Schedule engine additions by need**: Add queues, schedulers, and exports when observed operational load demands them.

## Rules

- **Never rewrite for fashion**: Keep the admin package bootable across majors; never swap stacks mid-journey without an ADR.
- **One admin UI only**: Multiple panels for the same data aggregate is an immediate defect.
- **Engine seams follow real pain**: Add queues for timeouts, indexes for slow queries, caches only after measurement.
- **No unverified backups**: A backup without a timed restore drill is a rumor.
- **Freeze public contracts**: Outbound exports and webhooks must be versioned; never mutate payload keys in place.

## Details

### Detect

- Ask is roadmap, maturity, or “did the UI blogs cover Laravel?”
- Not a `composer.json` signal. UI pack stays primary.

### First-party inventory

| Piece | First-party? | Where |
|---|---|---|
| Admin UI package | That package’s org | lock + its docs |
| Laravel engine | Yes | `vendor/laravel/framework` + docs |
| Year plan, roles, audit rows | No | app |

### Thin shape

```
one admin package → Resource/CRUD → model
year N adds one engine seam (queue, policy, export) — not a second admin UI
```

### Years (any web-app admin)

| Year | Job | Add | Do not |
|---|---|---|---|
| 1 Operate | Staff can run the business | One panel, roles, audit of writes, backups | Second admin UI, repository forest |
| 2 Trust | Mutations are safe | Policies, PII minimization, upload fields, queued mail, tests on authz | Client-only checks, raw dumps of users |
| 3 Scale | Work survives traffic | Queues + failed jobs, scheduler, DB filters, one cache after a measure | Collection-filter huge tables, new UI stack |
| 4 Platform | Others depend on you | Versioned export/API, team/tenant scope, outbound webhooks | Silent JSON renames, parallel portal |
| 5 Endure | The panel still boots | Major upgrade calendar, delete dead UIs, restore drill | Rewrite “because modern” |

### Scaffold

1. Detect UI pack; install that package only.
2. Ship year 1 before year 3 toys.
3. One behaviour test when a year adds auth, money, or a public contract.

### Dial-up

- Money, auth, uploads, PII, new export/API, tenancy, major upgrade.

### Slop smells

- Treating UI packs as the whole framework
- Two admin packages in one app
- Rewriting the panel every year
- Engine features with no failed-job or restore story

### Last lesson

- UI dialect ≠ Laravel coverage. Roadmap is this file; blogs are drafts, not the skill.
