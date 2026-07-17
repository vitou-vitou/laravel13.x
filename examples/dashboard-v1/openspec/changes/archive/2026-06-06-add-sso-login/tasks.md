## 1. Schema & config

- [x] 1.1 Migration: `sso_provider`, `sso_id` on users
- [x] 1.2 Google OAuth in `config/services.php` + `.env.example`

## 2. Backend

- [x] 2.1 `SsoAuthenticator` service (find/create/link user)
- [x] 2.2 `SsoController` redirect + callback
- [x] 2.3 Guest routes for SSO

## 3. UI

- [x] 3.1 Login page "Continue with Google" when configured

## 4. Tests

- [x] 4.1 Feature tests with `Socialite::fake`
- [x] 4.2 Full suite + verify-example
