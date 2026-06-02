# Browser verification — kindly-login-1122

**Date:** 2026-06-01  
**URL:** http://127.0.0.1:8011  
**Server:** `php artisan serve --host=127.0.0.1 --port=8011`

## Flow (all pass)

| Step | URL / action | Expected | Result |
|------|----------------|----------|--------|
| 1 | `GET /register` | Form loads | Pass |
| 2 | Register `browser-verify-20260601@example.com` | Redirect to dashboard | Pass |
| 3 | Dashboard | Heading **Kindly Login**, message “You're logged in to Kindly Login.” | Pass |
| 4 | Log out (user menu) | Session cleared | Pass |
| 5 | `GET /dashboard` as guest | Redirect to `/login` | Pass |
| 6 | Login same credentials | Dashboard with **Kindly Login** | Pass |

Screenshot: `kindly-login-dashboard-verified.png` (Cursor browser capture).

## Note

Document `<title>` still shows “Laravel” until `APP_NAME=Kindly Login` in `.env` for this serve instance; in-page branding is correct.
