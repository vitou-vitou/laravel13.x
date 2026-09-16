# ADR 0001: Separate Distinct Task Entities over Single Generic Task Table

## Context
The application requires three distinct core capabilities:
1. Company General tasks (internal operations, departmental assignments)
2. Daily Issue Resolution tasks (incident severity, root-cause tracking, resolution notes, resolution timestamps)
3. Customer General tasks (client contact details, external relationship tracking)

## Decision
Create dedicated models and migrations (`CompanyTask`, `DailyIssueTask`, `CustomerTask`) sharing standard lifecycle conventions rather than overloading a single polymorphic `tasks` table with nullable metadata columns.

## Status
Accepted.

## Consequences
- **Positive:** Explicit typing, clean schema migrations, dedicated Filament resources with specialized forms/filters, zero nullable column pollution.
- **Tradeoff:** Minimal duplication of basic status/title attributes across three tables.
