# Cambodia auto claims MVP implementation plan

Change: `openspec/changes/cambodia-auto-claims-mvp`

## Delivery order

1. Claim intake (`3.1`-`3.4`)
   - Files: `app/Enums/ClaimStatus.php`, `app/Models/Claim.php`, `database/migrations/*_create_claims_table.php`, `app/Http/Controllers/Api/ClaimController.php`, `routes/api.php`, `tests/Feature/ClaimIntakeTest.php`.
   - Acceptance: an authenticated policyholder can save, update, and submit only an active policy they own; submission returns a unique human-readable reference.
   - Verify: focused feature tests first, then `php artisan test`.
2. Claim documents (`4.1`-`4.5`)
   - Files: `ClaimDocument` model/migration, upload controller/routes, storage configuration, document feature tests.
   - Acceptance: allowed files are stored once per idempotency key, invalid files create no record, matching cross-claim hashes are flagged.
   - Verify: `Storage::fake()` feature tests and full suite.
3. Adjuster review and audit (`5.1`-`5.5`)
   - Files: review controller, policies, `ClaimStatusEvent` model/migration, transition tests.
   - Acceptance: staff see only their tenant queue; approve/reject/request-info transitions have validation and audit events.
   - Verify: role, tenant, and transition tests.
4. Policyholder status and notifications (`6.1`-`6.3`)
   - Files: timeline endpoint, notification classes, `lang/en`, `lang/km`, tests.
   - Acceptance: owners see ordered history only; material transitions notify in their selected locale.
   - Verify: timeline, authorization, and locale notification tests.
5. Payout tracking (`7.1`-`7.4`)
   - Files: `Payout` model/migration, finance controller, record-only gateway interface, tests.
   - Acceptance: finance records a pending payout only for an approved claim; marking it paid transitions the claim without calling an external rail.
   - Verify: payout state and notification tests.
6. Mobile client and hardening (`8.1`-`9.4`)
   - Files: localized views or API client surface, locale middleware, policies, seeders, README, full test suite.
   - Acceptance: Khmer-default mobile flow is usable, cross-tenant reads/writes are denied, demo instructions run locally.
   - Verify: full test suite, Pint, browser/Android QA where noted.

## Explicitly blocked

Tasks `10.1`-`10.3` remain deferred pending the insurer's payment-rail, regulator, and final currency decisions. The MVP records payouts only and supports the already-specified KHR/USD fields.
