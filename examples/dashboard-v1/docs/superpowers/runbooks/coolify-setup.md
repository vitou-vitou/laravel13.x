# Coolify Deploy Runbook — dashboard-v1

## 1. Provision droplet
- Vultr → Deploy → Cloud Compute, Ubuntu 24.04, 2 vCPU / 4 GB (~$24/mo).
- Add SSH key. Note public IP.

## 2. Install Coolify
SSH in, then:
```bash
curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash
```
Open `http://<droplet-ip>:8000`, create admin user.

## 3. Connect GitHub
- Coolify → Sources → GitHub → create GitHub App, install on the repo.
- This auto-adds the push webhook.

## 4. Create production application
- New Resource → Application → from the connected repo.
- Branch: `main`
- Build pack: **Dockerfile**
- Base directory / build context: `examples/dashboard-v1`
- Dockerfile location: `examples/dashboard-v1/Dockerfile`
- Add the Postgres + Redis as Coolify-managed databases OR deploy the `docker-compose.yml`.
  - Simplest: use Coolify "Docker Compose" build pack pointing at `examples/dashboard-v1/docker-compose.yml`.
- Domains: `app.yourdomain.com` (web :80), `ws.yourdomain.com` (reverb :8080).
- Healthcheck path: `/up`.

## 5. Secrets (production)
Set in Coolify → Environment Variables (mark as secret), using
`examples/dashboard-v1/.env.production.example` as the key list:
`APP_KEY` (generate: `php artisan key:generate --show`), `DB_PASSWORD`,
`REVERB_APP_ID/KEY/SECRET`, plus the production values from that template.

### 5a. Build-time args for the frontend (REQUIRED)

Vite bakes `VITE_*` into the JS bundle at build time, so these must be set as
**build arguments** (Coolify → app → Build → Build Variables), NOT just runtime env.
Without them the Reverb websocket client connects to nothing.
Set: `VITE_REVERB_APP_KEY` (= `REVERB_APP_KEY`), `VITE_REVERB_HOST` (= `ws.yourdomain.com`),
`VITE_REVERB_PORT=443`, `VITE_REVERB_SCHEME=https`, `VITE_APP_NAME`.

## 6. Create staging application
Repeat step 4 with branch `staging`, domains `staging.yourdomain.com` +
`ws-staging.yourdomain.com`, and its own secret set.

## 7. Enable auto-deploy
- In each app: turn on "Automatic Deployment" on push.
- Confirm webhook delivery in GitHub repo → Settings → Webhooks.

## 8. Backups
- Coolify → Postgres resource → Scheduled Backups → daily → S3 target
  (Vultr Object Storage credentials).

## 9. First deploy + smoke test
Push to `staging`, watch Coolify build logs, then run the smoke checklist
in the design spec section 6
(`docs/superpowers/specs/2026-06-07-vultr-coolify-cicd-design.md`).
