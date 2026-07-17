## Why

Password login only. Google SSO lets staff and customers sign in with an identity provider.

## What Changes

- Laravel Socialite + Google OAuth
- `sso_provider` / `sso_id` on users; link by email when account exists
- Login page "Continue with Google" when credentials configured
- New SSO users get `customer` role

## Capabilities

### New

- `sso-login`: Google OAuth redirect/callback
