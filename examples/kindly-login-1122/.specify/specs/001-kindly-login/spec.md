# Feature Specification: Kindly Login (Session Auth)

**Feature Branch**: `001-kindly-login`

**Created**: 2026-06-01

**Status**: MVP complete (2026-06-01)

**Input**: Standalone login app from Laravel 180+ catalog — full auth system (login-focused MVP). External review via **Arena.ai only**.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Register (Priority: P1)

A visitor creates an account with name, email, and password (with confirmation).

**Why this priority**: Without registration, no users can log in.

**Independent Test**: POST register → user exists → redirected to dashboard or home while authenticated.

**Acceptance Scenarios**:

1. **Given** valid data, **When** user registers, **Then** user is logged in and sees authenticated home.
2. **Given** duplicate email, **When** user registers, **Then** validation error without creating a second user.

---

### User Story 2 - Login (Priority: P1)

A registered user signs in with email and password.

**Why this priority**: Core product name and value.

**Independent Test**: Login with valid credentials → session established; invalid → stay guest with generic error.

**Acceptance Scenarios**:

1. **Given** valid credentials, **When** user logs in, **Then** session is authenticated.
2. **Given** wrong password, **When** user logs in, **Then** generic failure message (no email enumeration).
3. **Given** 5 failed attempts for the same email|IP key, **When** a 6th attempt, **Then** rate limit response (Breeze `LoginRequest`).

---

### User Story 3 - Logout (Priority: P1)

An authenticated user signs out.

**Independent Test**: After logout, protected routes redirect to login.

**Acceptance Scenarios**:

1. **Given** authenticated user, **When** logout, **Then** session cleared and login page shown.

---

### User Story 4 - Protected area (Priority: P2)

Authenticated users access a simple dashboard; guests are redirected to login.

**Acceptance Scenarios**:

1. **Given** guest, **When** visiting `/dashboard`, **Then** redirect to login.
2. **Given** authenticated user, **When** visiting `/dashboard`, **Then** 200 with welcome content.

### Edge Cases

- Remember-me cookie optional (Breeze default acceptable).
- Password minimum length enforced (`Password::defaults()` rules on register/update).
- CSRF on all auth forms.
- Failed login errors attach to **email** with `auth.failed` (no email enumeration).

### Security invariants

- Regenerate session on successful login **and** after registration auto-login; invalidate session and CSRF token on logout (`regenerateToken()`).
- Login rate limit key: `email|IP` composite (Breeze `LoginRequest`, max 5 attempts).
- Production cookies: secure, HTTP-only, SameSite (`lax` or `strict`) over HTTPS.
- Password reset links: signed + throttled (Breeze defaults).
- Registration/login rate limits documented; public deploy may add WAF/CAPTCHA later.

## Functional Requirements

- **FR-001**: System MUST register users with hashed passwords.
- **FR-002**: System MUST authenticate via session guard.
- **FR-003**: System MUST rate-limit login attempts.
- **FR-004**: System MUST protect dashboard from guests.
- **FR-005**: Failed login MUST use non-enumerating error copy (`auth.failed` on email field).
- **FR-006**: System MUST regenerate session after successful login.
- **FR-007**: Logout MUST invalidate session and regenerate CSRF token.

## Out of Scope (v1)

- OAuth / Socialite, 2FA, magic links, API tokens (Sanctum), admin roles
- Mandatory email verification before dashboard (Breeze stubs OK; not required for v1 MVP)

## Success Criteria

- **SC-001**: `php artisan test` covers register, login, logout, guest redirect.
- **SC-002**: Arena.ai spec review done (Prompt A); plan review in `docs/ARENA_REVIEW_PLAN.md` (see `docs/ARENA_LOOP.md`, chat in `docs/NEXT_SESSION.md`).
