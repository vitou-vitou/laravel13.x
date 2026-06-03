# Feature Specification: Facebook-Style Top Navigation (Study Clone)

**Feature Branch**: `001-fb-top-nav`

**Created**: 2026-06-03

**Status**: MVP complete (2026-06-03)

**Input**: User reference screenshot — dark desktop top bar with logo, search, five center tabs (Watch active), and right utility cluster (menu, messenger, notifications, profile).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - See the top navigation (Priority: P1)

A visitor lands on the demo and immediately recognizes the three-zone layout: brand/search left, primary tabs center, utilities right on a dark bar.

**Why this priority**: Without the bar, the project has no value.

**Independent Test**: GET `/` returns 200 with `role="banner"` nav containing Home, Watch, Marketplace, Groups, Gaming labels.

**Acceptance Scenarios**:

1. **Given** any primary route, **When** the page loads, **Then** the top navigation is visible with dark background styling.
2. **Given** the home route, **When** the page loads, **Then** Home is marked as the active primary tab.

---

### User Story 2 - Active tab indicator (Priority: P1)

When the user opens a primary section (e.g. Watch), that tab shows the filled blue icon treatment and bottom accent bar; other tabs stay outline style.

**Why this priority**: Active state is the main visual cue in the reference.

**Independent Test**: GET `/watch` marks Watch active with `aria-current="page"` and accent element.

**Acceptance Scenarios**:

1. **Given** `/watch`, **When** loaded, **Then** Watch link has `aria-current="page"` and visible active indicator.
2. **Given** `/marketplace`, **When** loaded, **Then** only Marketplace is active.

---

### User Story 3 - Accessible icon controls (Priority: P2)

Screen reader users hear clear names for search, each primary tab, and right-side utility buttons.

**Acceptance Scenarios**:

1. **Given** the nav, **When** inspected, **Then** Search, Menu, Messenger, Notifications, and Account menu controls have discernible accessible names.

---

### Edge Cases

- Narrow viewport: center tabs may scroll or compress; MVP may hide labels below `md` but keeps touch targets.
- Profile image: placeholder avatar acceptable; chevron overlay on avatar is decorative with `aria-hidden`.

## Functional Requirements

- FR-001: Single reusable top-nav component on all primary routes.
- FR-002: Routes: `/`, `/watch`, `/marketplace`, `/groups`, `/gaming`.
- FR-003: Active tab derived from current route name, not client-only state.
- FR-004: Right utilities are inert buttons/links for MVP (no modals required).
- FR-005: Reference screenshot stored under `docs/reference/` for visual QA.

## Out of Scope (MVP)

- Facebook login, Graph API, real notifications
- Mobile bottom tab bar
- Light theme toggle
