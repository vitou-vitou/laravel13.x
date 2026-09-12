---
description: "Users can track expenses and see spending patterns"
type: Project
about: "expense-tracker"
---

# expense-tracker

## What This Is

A simple web app where users log expenses by category and see monthly charts.

## Core Value

Users can track expenses and see spending patterns

## Current State

| Attribute | Value |
|-----------|-------|
| Type | Application |
| Version | 0.1.0 |
| Status | MVP shipped |
| Last Updated | 2026-09-12T21:20:00+07:00 |

## Requirements

### Core Features

- Log expenses
- Categorize expenses
- View monthly spending charts / totals

### Validated (Shipped)

- ✓ Log expenses — Phase 1
- ✓ Categorize expenses — Phase 1
- ✓ View monthly spending charts / totals — Phase 1

### Active (In Progress)
None yet.

### Planned (Next)

- Optional: edit/delete expenses, custom categories, export (not scheduled)

### Out of Scope

- User login / auth (MVP)

## Constraints

### Technical Constraints

- No login for MVP
- localStorage only

### Business Constraints

- None recorded

## Key Decisions

| Decision | Rationale | Date | Status |
|----------|-----------|------|--------|
| No auth in MVP | Keep scope small; prove expense + chart flow first | 2026-09-12 | Accepted |
| Vite + vanilla JS + localStorage | Smallest stack for MVP happy path | 2026-09-12 | Accepted |
| Chart.js via npm | One chart dependency; works offline after install | 2026-09-12 | Accepted |
| DOM list rendering | Harden against XSS (Aikido) | 2026-09-12 | Accepted |

## Success Metrics

| Metric | Target | Current | Status |
|--------|--------|---------|--------|
| MVP happy path | Add expense, pick category, see this month’s totals on a chart | Implemented | Shipped (browser smoke recommended) |

## Tech Stack / Tools

| Layer | Technology | Notes |
|-------|------------|-------|
| App | Vite + vanilla JS | examples/expense-tracker |
| Persist | localStorage | Key `expense-tracker:v1` |
| Charts | Chart.js (npm) | Monthly category doughnut |

---
*Last updated: 2026-09-12 after Phase 1*
