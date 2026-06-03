# Feature Specification: Facebook Reels Login Gate (Study Clone)

**Feature Branch**: `001-fb-reels-gate`

**Created**: 2026-06-03

**Status**: MVP complete (2026-06-03)

**Input**: Anonymous visit to `https://www.facebook.com/reel/879785385158813` — login modal over dimmed Reels shell (captured in `docs/reference/fb-reel-879785385158813.png`).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Reels route shows login gate (Priority: P1)

A visitor opens `/reel/{id}` and sees the Reels chrome plus centered “See more on Facebook” dialog.

**Independent Test**: GET `/reel/879785385158813` returns 200 with `data-reels-gate="true"` and dialog semantics.

**Acceptance Scenarios**:

1. **Given** the reel URL, **When** the page loads, **Then** “Reels” label and login modal are visible.
2. **Given** the modal, **When** inspected, **Then** it has `role="dialog"` and `aria-modal="true"`.

---

### User Story 2 - Accessible login form (Priority: P1)

Form fields and actions have labels and discernible names for assistive tech.

**Acceptance Scenarios**:

1. **Given** the modal, **When** inspected, **Then** email/password fields have stable ids and Close has `aria-label="Close login dialog"`.

---

### User Story 3 - Public header chrome (Priority: P2)

Logged-out Facebook header (wordmark + inline login) appears above the gated content.

**Acceptance Scenarios**:

1. **Given** the page, **When** loaded, **Then** Facebook branding link has `aria-label="Facebook"`.

---

## Functional Requirements

- FR-001: Route `/reel/{reelId}` renders study page; `/` redirects to demo id `879785385158813`.
- FR-002: No real Facebook API, OAuth, or video playback.
- FR-003: Reference screenshot under `docs/reference/`.
- FR-004: Forms are inert (`action="#"`); no credential handling.

## Out of Scope (MVP)

- Authenticated reel player, comments, like/share
- Graph API or embedding facebook.com iframes
- Mobile bottom navigation
