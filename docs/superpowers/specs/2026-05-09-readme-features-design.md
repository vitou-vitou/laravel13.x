# Design: Root README features summary

**Status:** Approved (user confirmed placement + bullet approach 2026-05-09).  
**Scope:** `README.md` at repository root only. No changes under `examples/` or `apps/*/README.md`.

## Goal

Add scannable **Features** section so newcomers see what the monorepo contains before the per-app list and bootstrap commands.

## Placement

Insert `### Features` immediately after the intro paragraph under `## Monorepo Apps Layout` (after *This repository now uses...*) and before `### Apps`, so `### Apps` and bootstrap subsections stay peers under the monorepo `##` heading.

## Content

Bullet list (sentence case, factual, no marketing):

- Laravel 13 skeleton at repo root with Vite and Tailwind v4, including a custom welcome page.
- Runnable Laravel apps under `apps/`, each with its own Composer install and environment.
- Reference Filament project in `examples/basic-laravel-filamentphp` (admin panel, resources, widgets); same stack mirrored in `apps/app-11-basic-filament`.
- Sample SaaS-style app in `apps/app-12-post-saas` (workspaces and posts).
- Placeholder directories `apps/app-01` through `apps/app-10` reserved for new projects.

## Non-goals

- Replacing or shortening the stock **About Laravel** section.
- Documenting implementation details (Filament CTA, graphify, internal tooling).
- Updating README files inside `examples/` or `apps/` in this change.

## Maintenance

When a flagship app is added or renamed, update this bullet list to match `### Apps`.
