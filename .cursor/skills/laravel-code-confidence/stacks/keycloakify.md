# Keycloakify

## Detect
- `keycloakify` dependency · Keycloak theme build · `keycloakify-starter` patterns · theme output jars/folders for Keycloak

## First-party inventory
| Piece | First-party? | Where |
|---|---|---|
| Keycloakify | First-party **Keycloakify** | keycloakify.dev / GitHub keycloakify |
| Keycloak server | First-party **Keycloak**/Red Hat | keycloak.org docs |
| Your theme pages / i18n / branding | No | theme source |

## Thin shape
```
Keycloakify pages/components → Keycloak theme build → deploy to Keycloak
```

Stay inside Keycloakify’s page/account/login freemarker-replacement model — don’t invent a parallel auth SPA unless product says so.

## Scaffold
1. Official Keycloakify starter for current major (docs)
2. Match starter storybook/dev login flow if present

## Dial-up
- Custom authenticators · account console vs login theme · multi-realm branding · CSP

## Slop smells
- Treating Keycloakify as “just React CRA” and breaking theme build
- Bypassing Keycloakify APIs with raw FreeMarker hacks without need
- Copying Laravel/Inertia auth flows into the theme
- Pinning random forks instead of documented Keycloakify releases

## Last lesson
- Auth UI confidence = Keycloakify + Keycloak docs, not Laravel packs
