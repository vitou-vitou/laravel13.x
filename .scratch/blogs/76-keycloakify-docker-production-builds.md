---
title: "How to Build Production Docker Images for Keycloak with Keycloakify Themes"
published: true
description: "Package Keycloakify theme JARs into production Keycloak containers with multi-stage Docker builds and host-side ZIP verification gates."
tags: "keycloak, keycloakify, docker, devops, security"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/76-keycloakify-docker-production-builds.md"
---

Deploying a custom Keycloak theme to production is more than copying a compiled bundle into a container. If a corrupted JAR, empty archive, or un-optimized asset bundle reaches a running Keycloak cluster, the instance can fail to boot or fall back to raw FreeMarker templates without warning.

A resilient deployment pipeline uses multi-stage Docker builds to compile the theme, validates the JAR archive before staging, and optimizes the container for production runtimes. Let's see how.

## The Problem: Late Failures in Keycloak Deployments

Keycloak processes providers and theme JARs during container initialization:

```text
2026-09-28 09:12:11,402 ERROR [org.keycloak.quarkus.runtime.KeycloakMain] (main) Failed to start server: java.util.zip.ZipException: zip END header not found
```

If a build step produces an incomplete JAR or network glitch during a file transfer, Keycloak crashes on startup. In Kubernetes or containerized clusters, this causes crash loops and blocks rollouts.

Catching these issues early requires validating the archive before container staging.

## Step 1: Pre-Build JAR Validation Gate

Before copying JAR files into container image layers, run a host-side check to verify the archive's integrity and ZIP magic bytes (`PK`):

`docker/validate-providers.sh:`
```bash
#!/usr/bin/env bash
set -u

PROVIDERS_DIR="${1:-./providers}"

if [ ! -d "$PROVIDERS_DIR" ]; then
  echo "[-] Providers directory missing at $PROVIDERS_DIR"
  exit 1
fi

shopt -s nullglob
jars=( "$PROVIDERS_DIR"/*.jar )
shopt -u nullglob

if [ "${#jars[@]}" -eq 0 ]; then
  echo "[-] No JAR files detected in $PROVIDERS_DIR"
  exit 1
fi

for jar in "${jars[@]}"; do
  name="$(basename "$jar")"

  if [ ! -s "$jar" ]; then
    echo "[-] $name is empty (0 bytes)"
    exit 1
  fi

  # Verify standard PK zip magic bytes at the beginning of the file
  magic=$(head -c 2 "$jar" | od -An -c | tr -d ' \n')
  if [ "$magic" != "PK" ]; then
    echo "[-] $name failed ZIP header check (got '$magic', expected 'PK')"
    exit 1
  fi

  size=$(wc -c <"$jar" | tr -d ' ')
  echo "[+] $name verified ($size bytes, valid ZIP magic header)"
done

echo "[+] Provider and Theme JAR validation passed."
exit 0
```

Integrating this script into your CI/CD pipeline catches corrupt or empty theme archives before Docker builds begin.

## Step 2: Multi-Stage Dockerfile Architecture

A production-ready Dockerfile uses a dedicated Node.js build stage to compile the React assets, then copies the resulting theme JAR into an optimized Keycloak container:

`Dockerfile:`
```dockerfile
# ---- STAGE 1: Keycloakify Theme Build ----------------------------------------
FROM node:20-alpine AS theme-builder
WORKDIR /app

# Cache package dependencies
COPY package*.json ./
RUN npm ci

# Copy source and compile theme JAR
COPY . .
RUN npm run build

# ---- STAGE 2: Optimized Keycloak Runtime -------------------------------------
FROM quay.io/keycloak/keycloak:26.4.7 AS builder

WORKDIR /opt/keycloak

# Stage custom Keycloakify theme JAR from the builder stage
COPY --from=theme-builder /app/dist_keycloak/*.jar /opt/keycloak/providers/

# Enable health checks, metrics, and pre-build Quarkus runtime optimizations
ENV KC_HEALTH_ENABLED=true
ENV KC_METRICS_ENABLED=true
ENV KC_FEATURES=docker

# Run build step to bake providers into Quarkus runtime
RUN /opt/keycloak/bin/kc.sh build --db=postgres

# ---- STAGE 3: Final Production Image -----------------------------------------
FROM quay.io/keycloak/keycloak:26.4.7
COPY --from=builder /opt/keycloak/ /opt/keycloak/

USER keycloak
ENTRYPOINT ["/opt/keycloak/bin/kc.sh", "start", "--optimized"]
```

Running `/opt/keycloak/bin/kc.sh build` during the build stage pre-indexes the theme JAR and providers, significantly reducing container startup times in production.

## Step 3: Automate Realm Theme Assignment

Once deployed, set your custom theme as the default for your realm using the Keycloak Admin CLI or REST API:

`scripts/apply-theme.sh:`
```bash
#!/usr/bin/env bash
set -euo pipefail

REALM="${1:-production}"
THEME="${2:-enterprise-theme}"
KC_HOST="${KEYCLOAK_URL:-http://localhost:8080}"

# Obtain administrative token
TOKEN=$(curl -s -X POST "$KC_HOST/realms/master/protocol/openid-connect/token" \
  -d "client_id=admin-cli" \
  -d "username=$ADMIN_USER" \
  -d "password=$ADMIN_PASS" \
  -d "grant_type=password" | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)

# Update realm login and account themes
curl -s -X PUT "$KC_HOST/admin/realms/$REALM" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"loginTheme\":\"$THEME\",\"accountTheme\":\"$THEME\"}"

echo "[+] Successfully activated theme '$THEME' on realm '$REALM'"
```

Automating this configuration ensures new environments immediately use the custom theme without manual intervention in the admin console.

## What Can Go Wrong

- **Omitting `kc.sh build --optimized`**: Running Keycloak without pre-building Quarkus forces the container to re-scan and index theme JARs on every boot, adding 15–30 seconds to container startup time.
- **Root Permissions**: Running Keycloak as the root user introduces security risks and fails in restricted container environments like OpenShift. Ensure the final stage uses `USER keycloak`.

## Summary

Packaging Keycloakify themes into production containers requires strict validation gates and optimized container configurations. By verifying JAR files before build time and running `kc.sh build` during image creation, teams ensure reliable deployments and fast container startups.

## Further Reading

- [Keycloak Container Optimization Guide](https://www.keycloak.org/server/containers)
- [Quarkus Fast-Startup Rationale](https://quarkus.io/guides/quarkus-runtime-architecture)
- [Docker Multi-Stage Build Best Practices](https://docs.docker.com/build/building/multi-stage/)

Implement pre-build validation and multi-stage Docker builds to make your Keycloak theme deployments robust and reproducible.
