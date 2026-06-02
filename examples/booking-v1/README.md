# booking-v1

General **appointment booking** (180+ catalog #125), built with **Spec-Kit + Superpowers** (TDD).

**Resume next session:** [docs/NEXT_SESSION.md](docs/NEXT_SESSION.md)

## Spec-Kit artifacts

| File | Purpose |
|------|---------|
| `.specify/memory/constitution.md` | Project rules |
| `.specify/specs/001-appointment-booking/spec.md` | What / why |
| `.specify/specs/001-appointment-booking/plan.md` | How |
| `.specify/specs/001-appointment-booking/tasks.md` | Task checklist |

## Grok loop (optional)

Grok requires a signed-in xAI session. See [docs/GROK_LOOP.md](docs/GROK_LOOP.md).

## Run locally

```bash
cd examples/booking-v1
cp .env.example .env   # if needed
php artisan migrate
php artisan test
```

API auth (Sanctum bearer token):

- `POST /api/register` — returns `data.token`
- `POST /api/login` — returns `data.token`
- `POST /api/logout` — revokes current token
- Send `Authorization: Bearer {token}` on protected routes below

Protected booking API:

- `POST /api/provider/setup` — create service + availability
- `GET /api/services/{id}/slots`
- `POST /api/services/{id}/bookings/hold`
- `POST /api/bookings/{id}/confirm`
- `POST /api/bookings/{id}/cancel`
- `GET /api/my/bookings`

```bash
php artisan bookings:release-expired-holds
```
