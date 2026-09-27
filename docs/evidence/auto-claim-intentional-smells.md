# Intentional smells — auto-claim-dashboard

Open **after** OCR review. Score: did OCR find these?

App: `examples/auto-claim-dashboard`

| # | Smell | Where |
|---|---|---|
| 1 | God controller (login, CRUD, search, JSON API) | `app/Http/Controllers/ClaimDashboardController.php` |
| 2 | Business rules in Blade (late filing, status colors, can-file) | `resources/views/claims/dashboard.blade.php`, `show.blade.php` |
| 3 | Copy-pasted / duplicated claim loops + status counting | `dashboard()`, `show()` related loop |
| 4 | Weak/missing server validation on store/login | `store()`, `doLogin()` |
| 5 | Magic status strings (`pending` / `submitted` / `closed`) | controller + views |
| 6 | N+1 / load-all-then-filter in PHP (`Claim::all()` then foreach) | `dashboard()`, `show()` related |
| 7 | Secrets in source (password, API token) | controller props, `config/claim_auth.php`, login form defaults, Blade token |
| 8 | CSRF-ish / GET mutates state | `GET /claims/{id}/submit` → `submitClaimGet` |
| 9 | XSS / unescaped output (`{!! !!}`) | login email, description, token, search `q` |
| 10 | Fat inline JS globals (`API_TOKEN`, `ALL_CLAIMS`, interval poll) | `dashboard.blade.php` scripts |
| + | SQL injection in search concatenation | `search()` |
| + | IDOR: show any claim by id; dashboard lists other users' claims | `show()`, `dashboard` `otherPeopleClaims` |
| + | Auto-login without auth (`session uid` defaulted) | `dashboard()` |
| + | Raw SQL insert mixed with Eloquent | `store()` |
| + | Mass assignment wide open (`$guarded = []`) | `Claim` model |

## Expected OCR categories

- **critical/high security**: SQLi, XSS, hardcoded secrets, IDOR, GET state change
- **medium maintainability**: god class, Blade business logic, magic strings
- **medium/high performance**: Claim::all + PHP filter, N+1 in `listRaw`

## How to score

1. Run `ocr review` on the working tree (uncommitted example).
2. Mark each row Found / Missed.
3. Note time-to-first-critical and total review duration.
