---
title: "How to Build a Continuous Delivery Pipeline for Keycloakify Themes"
published: true
description: "Automate Keycloakify theme testing, JAR artifact generation, and zero-downtime rolling container deployments with GitHub Actions."
tags: "keycloak, keycloakify, github-actions, ci-cd, devops"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/79-keycloakify-continuous-delivery-pipeline.md"
---

Manual theme deployments—such as building a JAR on a local machine, transferring it over SCP, and restarting Keycloak services by hand—risk production outages and inconsistent container states. A reliable deployment workflow automates theme verification, builds versioned release artifacts, and rolls out container updates continuously.

A robust CI/CD pipeline tests the React theme, packages the JAR archive, and automates zero-downtime container rollouts. Let's see how.

## The Deployment Pipeline Architecture

A dependable Keycloakify delivery pipeline comprises three core stages:

1. **Continuous Integration**: Linting, TypeScript checks, and Storybook build verification.
2. **Artifact Packaging**: Compiling the Keycloak theme JAR and validating ZIP magic headers.
3. **Container Delivery**: Building the production Keycloak container image, pushing it to a registry, and triggering a rolling deployment.

## Step 1: Automate Build and JAR Packaging in GitHub Actions

Define a comprehensive workflow that runs on every push to the default branch:

`.github/workflows/deploy-keycloak-theme.yml:`
```yaml
name: Deliver Keycloak Theme

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  verify-and-package:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout Source Code
        uses: actions/checkout@v4

      - name: Setup Node.js Runtime
        uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: 'npm'

      - name: Install Dependencies
        run: npm ci

      - name: TypeScript Static Check
        run: npx tsc --noEmit

      - name: Build Theme JAR
        run: npm run build

      - name: Validate Generated Theme JAR
        run: |
          JAR_FILE=$(find dist_keycloak -name "*.jar" | head -n 1)
          if [ -z "$JAR_FILE" ]; then
            echo "[-] No JAR file generated in dist_keycloak/"
            exit 1
          fi
          
          # Check ZIP magic bytes (PK)
          MAGIC=$(head -c 2 "$JAR_FILE" | od -An -c | tr -d ' \n')
          if [ "$MAGIC" != "PK" ]; then
            echo "[-] JAR corrupted. Expected PK header, got: $MAGIC"
            exit 1
          fi
          echo "[+] Successfully verified valid JAR: $JAR_FILE"

      - name: Upload Theme Artifact
        uses: actions/upload-artifact@v4
        with:
          name: keycloak-theme-jar
          path: dist_keycloak/*.jar
          retention-days: 7
```

Running TypeScript verification before theme compilation catches broken context bindings and missing translation keys before packaging begins.

## Step 2: Build and Push Production Container

Add a deployment job that runs after artifact packaging:

`.github/workflows/deploy-keycloak-theme.yml:`
```yaml
  publish-container:
    needs: verify-and-package
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest

    steps:
      - name: Checkout Source Code
        uses: actions/checkout@v4

      - name: Download Theme Artifact
        uses: actions/download-artifact@v4
        with:
          name: keycloak-theme-jar
          path: providers/

      - name: Log in to Container Registry
        uses: docker/login-action@v3
        with:
          registry: ghcr.io
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Build and Push Keycloak Image
        uses: docker/build-push-action@v5
        with:
          context: .
          file: ./Dockerfile
          push: true
          tags: |
            ghcr.io/${{ github.repository }}/keycloak:latest
            ghcr.io/${{ github.repository }}/keycloak:${{ github.sha }}
```

This stage stages the verified JAR into the container context and publishes an immutable, versioned image tag.

## Step 3: Trigger Rolling Deployments

For container orchestration platforms (Kubernetes, AWS ECS, or Render), trigger a rolling update with zero downtime:

`scripts/deploy-rollout.sh:`
```bash
#!/usr/bin/env bash
set -euo pipefail

DEPLOYMENT_NAME="keycloak-iam"
NAMESPACE="identity-production"
IMAGE_TAG="$1"

echo "[+] Initiating rolling update to image: $IMAGE_TAG"

# Update container image in Kubernetes deployment
kubectl set image "deployment/$DEPLOYMENT_NAME" \
  "keycloak=$IMAGE_TAG" \
  -n "$NAMESPACE"

# Monitor rollout until all replica pods report ready
kubectl rollout status "deployment/$DEPLOYMENT_NAME" -n "$NAMESPACE"

echo "[+] Deployment completed successfully with zero authentication downtime."
```

Because Keycloak instances share a common database and distributed cache (Infinispan), rolling updates allow instances running the new theme to come online while old instances terminate gracefully.

## What Can Go Wrong

- **Database Schema Contention**: If a Keycloak version upgrade accompanies a theme deployment, multiple pods booting simultaneously may attempt concurrent schema migrations. Always isolate theme changes from major Keycloak version upgrades.
- **Aggressive Browser Caching**: If CDNs cache theme assets indefinitely without cache-busting hashes in asset URLs, users may see outdated stylesheets after a deployment. Keycloakify includes build hashes in resource filenames to ensure smooth cache invalidation.

## Summary

Automating theme delivery through continuous integration eliminates manual deployment risks. By enforcing TypeScript verification, validating JAR integrity in CI, and triggering rolling container rollouts, engineering teams can ship authentication UI updates safely and reliably.

## Further Reading

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Keycloak Zero-Downtime Upgrades](https://www.keycloak.org/high-availability/concepts-memory-replication)
- [Docker Build and Push Actions](https://github.com/docker/build-push-action)

Set up a GitHub Actions workflow in your theme repository to automate testing, packaging, and zero-downtime deployments.
