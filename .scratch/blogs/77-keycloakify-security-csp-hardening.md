---
title: "How to Secure Keycloakify Themes Against XSS and CSP Violations"
published: true
description: "Harden React Keycloakify authentication themes using strict Content Security Policy headers, nonce bindings, and safe sanitized error handling."
tags: "keycloak, keycloakify, security, csp, xss"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/77-keycloakify-security-csp-hardening.md"
---

Authentication pages are prime targets for credential harvesting and cross-site scripting (XSS) attacks. Because Keycloakify compiles modern React code—which relies on dynamic JavaScript bundles and inline styling—developers sometimes loosen Content Security Policy (CSP) headers, unintentionally exposing login flows to script injection vulnerabilities.

Hardening a Keycloakify theme requires understanding how Keycloak delivers CSP nonces and configuring React to avoid unsafe evaluation patterns. Let's see how.

## The Threat Vector: Loosened CSP in Auth Screens

When styling or scripting errors occur in custom themes, developers often add `unsafe-inline` or `unsafe-eval` to Keycloak's CSP headers:

```text
# UNSAFE CONFIGURATION:
content-security-policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval';
```

If an attacker injects content through unescaped user attributes or query parameters, `unsafe-inline` allows the malicious script to execute directly in the authentication context. The script can intercept keystrokes in the password field and exfiltrate credentials before form submission.

A secure Keycloak theme enforces strict script origin restrictions and binds dynamic script execution to cryptographically unique nonces.

## Step 1: Bind React to Keycloak's CSP Nonce

Keycloak generates a unique cryptographic nonce per HTTP request and exposes it in the FreeMarker context as `kcContext.nonce`. Keycloakify provides this value through `kcContext` to bind your React script tags securely:

`src/main.tsx:`
```tsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { getKcContext } from './login/KcContext';
import KcApp from './login/KcApp';
import './main.css';

const { kcContext } = getKcContext();

// Ensure dynamic chunk imports utilize Keycloak's server-injected CSP nonce
if (kcContext?.nonce) {
    __webpack_nonce__ = kcContext.nonce;
}

const rootElement = document.getElementById('root');

if (rootElement) {
    ReactDOM.createRoot(rootElement).render(
        <React.StrictMode>
            {kcContext ? (
                <KcApp kcContext={kcContext} />
            ) : (
                <div className="p-8 text-center text-slate-600">
                    Standalone React Preview Mode
                </div>
            )}
        </React.StrictMode>
    );
}
```

Setting `__webpack_nonce__` (supported by both Webpack and Vite's script injector) attaches the server-generated nonce to any dynamic script chunks created at runtime.

## Step 2: Prevent XSS in Error Message Rendering

Keycloak error messages can sometimes contain raw user input or unescaped parameter reflections. Avoid using `dangerouslySetInnerHTML` when displaying server validation errors:

`src/login/components/ErrorMessage.tsx:`
```tsx
type ErrorMessageProps = {
    message?: string;
};

export default function ErrorMessage({ message }: ErrorMessageProps) {
    if (!message) return null;

    return (
        <div
            role="alert"
            className="flex items-center gap-2 p-3 text-sm text-red-800 border border-red-200 rounded-lg bg-red-50"
        >
            <svg className="w-4 h-4 shrink-0 fill-current" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
            </svg>
            {/* Safe text node rendering prevents DOM injection */}
            <span className="font-medium">{message}</span>
        </div>
    );
}
```

Rendering error text as plain text nodes ensures that any HTML entities or script tags in the error message are displayed safely rather than executed.

## Step 3: Configure Strict Realm CSP Headers

Enforce strict Content Security Policy directives in your Keycloak realm configuration:

`theme.properties:`
```properties
# Strictly bind scripts and styles to self and Keycloak nonces
kcCspHeader=default-src 'none'; script-src 'self' 'nonce'; style-src 'self' 'nonce'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; form-action 'self';
```

Alternatively, configure the realm's security headers directly via the Keycloak Admin CLI:

`scripts/harden-realm-csp.sh:`
```bash
#!/usr/bin/env bash
set -euo pipefail

REALM="${1:-production}"
CSP="default-src 'none'; script-src 'self'; style-src 'self'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none';"

TOKEN=$(bash scripts/get-admin-token.sh)

curl -s -X PUT "$KEYCLOAK_URL/admin/realms/$REALM" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"browserSecurityHeaders\":{\"contentSecurityPolicy\":\"$CSP\"}}"

echo "[+] Strict CSP headers applied to realm: $REALM"
```

This policy prevents authentication forms from being embedded in malicious iframes (`frame-ancestors 'none'`) and restricts form submissions exclusively to the Keycloak domain (`form-action 'self'`).

## What Can Go Wrong

- **Using External CDN Scripts**: Loading fonts, Google Tag Manager, or tracking libraries directly from external CDNs introduces third-party dependencies into the authentication boundary. Self-host all fonts and icons inside your theme package to keep the CSP policy strict and self-contained.
- **Breaking Nonce Extraction in Dev Mode**: During local Vite testing, `kcContext.nonce` is undefined. Ensure your application handles the absence of the nonce gracefully so local development workflows continue to function.

## Summary

Securing authentication interfaces requires strict Content Security Policies and defensive coding practices. By attaching Keycloak's cryptographic nonces to dynamic script tags, avoiding `dangerouslySetInnerHTML`, and self-hosting all theme assets, Keycloakify themes remain resilient against injection and exfiltration attacks.

## Further Reading

- [MDN Content Security Policy (CSP) Guide](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [Keycloak Security Headers Reference](https://www.keycloak.org/docs/latest/server_admin/#_headers)
- [OWASP Cross-Site Scripting (XSS) Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)

Audit your Keycloak realm's CSP headers and theme markup to ensure login screens remain safe against script injection.
