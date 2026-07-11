
# TelegramAuth

Multi-tenant B2B SaaS that lets any company add **Log in with Telegram** for their customers.

## Features

- Tenant registration and onboarding wizard
- Telegram Login Widget + OIDC (`oauth.telegram.org`) flows
- Authorization code + PKCE token exchange
- Encrypted bot token storage, HMAC verification, audit logs, rate limiting

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

Register at `/register`, complete onboarding, then integrate using the snippet from the dashboard.

## Auth flow

1. Redirect customer to `/auth/start?client_id=...&redirect_uri=...&state=...&code_challenge=...`
2. Customer authenticates via Telegram widget
3. Platform redirects to your `redirect_uri` with `code` and `state`
4. Exchange code at `POST /oauth/token` with `code_verifier`, `client_id`, `client_secret`
5. Use `access_token` at `GET /oauth/userinfo`

## Tests

```bash
php artisan test
```
