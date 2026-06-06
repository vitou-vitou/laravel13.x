## Why

SSO tunnel setup is CLI-only. Staff need named ngrok profiles in Filament with one-click activation and OAuth URL sync.

## What Changes

- `tunnels` table + Filament CRUD under Development navigation
- Spatie permission `manage_dev_tunnels`
- Activate action syncs `NGROK_DEV_DOMAIN` and OAuth redirect URIs to `.env`
- Verify action probes tunnel health (login page)

## Capabilities

### New

- `dev-tunnel-profiles`: CRUD tunnel profiles, single active profile, env sync, health verify

## Non-goals

- Start/stop ngrok from PHP
- Change `APP_URL` away from Herd `.test`
