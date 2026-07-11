# TelegramAuth — next session

**Path:** `examples/telegram-login`  
**URL:** `http://telegram-login.test`  
**Branch copied from:** `feature/telegram-login-saas-9127`

## MVP status

Multi-tenant B2B "Log in with Telegram" platform:

- Tenant portal (register, onboarding, dashboard)
- Widget OAuth flow with `#tgAuthResult` hash capture
- PKCE auth code + token exchange + JWT userinfo
- Demo shop at `/demo-shop`

## Verify

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/telegram-login
composer install
./../../bin/fix-example-app-key telegram-login
herd link telegram-login
php artisan migrate --force
php artisan test
```

Or from repo root: `./bin/verify-example telegram-login`

## BotFather setup

1. `/setdomain` → your bot → `telegram-login.test` (hostname only)
2. Onboarding domain field must match `APP_URL` host

## Optional next work

- OIDC flow hardening with BotFather Web Login Allowed URLs
- Stripe billing / tenant plans
- OpenSpec post-MVP changes
