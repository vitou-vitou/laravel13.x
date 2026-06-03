# Implementation Plan: FB Top Nav Clone

**Branch**: `001-fb-top-nav` | **Date**: 2026-06-03 | **Spec**: [spec.md](./spec.md)

## Summary

Laravel 13 + Blade + Tailwind 4 demo that reproduces the Facebook desktop top navigation from the user reference image. Route-based active tab; PHPUnit asserts structure and a11y.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13.x  
**Primary Dependencies**: Tailwind 4 via Vite (skeleton default)  
**Storage**: None for MVP  
**Testing**: `tests/Feature/FbTopNavTest.php`  
**Target Platform**: Web (static pages)  
**Constraints**: Spec-Kit + Superpowers; no OpenSpec at init  

## Constitution Check

- [x] Reference-only study (no Meta APIs)
- [x] TDD for nav + active state
- [x] YAGNI — no Breeze/auth
- [x] DESIGN.md tokens for FB dark bar

## Project Structure

```text
examples/clone-the-fb-nav/
├── .specify/specs/001-fb-top-nav/
├── config/fb-nav.php              # primary tabs metadata
├── docs/DESIGN.md
├── docs/reference/                # screenshot copy
├── resources/views/
│   ├── layouts/fb.blade.php
│   ├── components/fb-top-nav.blade.php
│   └── pages/*.blade.php
├── routes/web.php
└── tests/Feature/FbTopNavTest.php
```

## Routes

| Route | Name | Active tab |
|-------|------|------------|
| `/` | `home` | home |
| `/watch` | `watch` | watch |
| `/marketplace` | `marketplace` | marketplace |
| `/groups` | `groups` | groups |
| `/gaming` | `gaming` | gaming |

## Visual tokens (from reference)

| Token | Value |
|-------|-------|
| Nav background | `#242526` |
| FB blue (active) | `#1877F2` |
| Icon button bg | `#3A3B3C` |
| Nav height | `56px` (`h-14`) |
| Active indicator | `3px` bottom bar, FB blue |

## Test matrix (MVP)

| Test | Asserts |
|------|---------|
| `test_home_page_renders_fb_top_nav` | 200, banner, five tab labels |
| `test_watch_route_marks_watch_active` | `aria-current` on Watch |
| `test_marketplace_route_marks_marketplace_active` | active swap |
| `test_utility_controls_have_accessible_names` | Search, Menu, Messenger, Notifications |
