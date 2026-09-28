---
title: "How to Build Custom Keycloak Themes with React and Keycloakify"
published: true
description: "Replace legacy FreeMarker .ftl templates with a modern React, TypeScript, and Tailwind CSS development workflow for Keycloak."
tags: "keycloak, keycloakify, react, authentication"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/68-keycloakify-getting-started-react.md"
---

Customizing default Keycloak login screens typically means editing raw FreeMarker (`.ftl`) templates. Without TypeScript checks, component modularity, or modern CSS tooling, simple branding adjustments quickly result in broken redirects or silent authentication failures.

Keycloakify bridges modern React and Keycloak's server-side authentication engine. It compiles React applications directly into standard Keycloak theme JAR files. Let's see how.

## The Architecture: Why FreeMarker Fails Frontend Teams

Keycloak renders authentication screens on the server. When a browser initiates an OpenID Connect (OIDC) flow, Keycloak serves HTML containing security tokens, active session parameters, and realm policies.

Traditional custom themes require writing FreeMarker templates like this:

`theme/login-theme/login/login.ftl:`
```html
<#import "template.ftl" as layout>
<@layout.registrationLayout displayInfo=social.displayInfo; section>
    <#if section = "header">
        ${msg("loginTitle")}
    <#elseif section = "form">
        <form id="kc-form-login" action="${url.loginAction}" method="post">
            <input id="username" name="username" value="${(login.username!'')}" type="text" />
            <input id="password" name="password" type="password" />
            <input type="submit" value="${msg('doLogIn')}" />
        </form>
    </#if>
</@layout.registrationLayout>
```

Testing this markup requires deploying a live Keycloak container, initiating a session, and visually checking the output. Modifying layouts or adding client-side form behavior is cumbersome and brittle.

## The Keycloakify Alternative: Server-Injected Context

Keycloakify does not build a client-side Single Page Application (SPA). Instead, Keycloak compiles your React bundle into static assets and generates tiny FreeMarker wrappers. When Keycloak serves a page, it serializes its execution context into a global JavaScript object: `window.kcContext`.

Your React entry point extracts this data and hydrates the matching page component:

`src/login/KcPage.tsx:`
```tsx
import { Suspense, lazy } from 'react';
import type { KcContext } from './KcContext';

const Login = lazy(() => import('./pages/Login'));
const Register = lazy(() => import('./pages/Register'));

export default function KcPage(props: { kcContext: KcContext }) {
    const { kcContext } = props;

    return (
        <Suspense fallback={<div className="p-8 text-center">Loading authentication form...</div>}>
            {(() => {
                switch (kcContext.pageId) {
                    case 'login.ftl':
                        return <Login kcContext={kcContext} />;
                    case 'register.ftl':
                        return <Register kcContext={kcContext} />;
                    default:
                        return <div>Page not customized: {kcContext.pageId}</div>;
                }
            })()}
        </Suspense>
    );
}
```

This dispatcher matches Keycloak's requested route and renders the corresponding component with type-safe access to realm data and submission URLs.

## Building a Type-Safe Login Component

Every form in Keycloak must submit standard credentials via HTTP POST to `kcContext.url.loginAction`. This preserves Keycloak's session security, CSRF protection, and cookies.

`src/login/pages/Login.tsx:`
```tsx
import type { KcContext } from '../KcContext';

type LoginPageProps = {
    kcContext: Extract<KcContext, { pageId: 'login.ftl' }>;
};

export default function Login({ kcContext }: LoginPageProps) {
    const { url, realm, messagesPerField } = kcContext;

    return (
        <div className="min-h-screen flex items-center justify-center bg-slate-100 p-4">
            <div className="w-full max-w-md bg-white rounded-lg shadow-md p-6 border border-slate-200">
                <h1 className="text-xl font-bold text-slate-900 mb-4">
                    Sign in to {realm.displayName || 'Portal'}
                </h1>

                <form action={url.loginAction} method="post" className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-700">Username or Email</label>
                        <input
                            name="username"
                            type="text"
                            defaultValue={kcContext.login?.username ?? ''}
                            required
                            className="w-full mt-1 px-3 py-2 border rounded border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        {messagesPerField.existsError('username') && (
                            <span className="text-xs text-red-600 mt-1 block">
                                {messagesPerField.getFirstError('username')}
                            </span>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">Password</label>
                        <input
                            name="password"
                            type="password"
                            required
                            className="w-full mt-1 px-3 py-2 border rounded border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <button
                        type="submit"
                        className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition"
                    >
                        Sign In
                    </button>
                </form>
            </div>
        </div>
    );
}
```

Because the form points to `url.loginAction` with native POST submission, standard browser behaviors and Keycloak cookie storage remain intact.

## Packaging into a Theme JAR

Keycloakify builds the production bundle and bundles it into an archive compatible with Keycloak's provider directory:

`package.json:`
```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build && keycloakify build-keycloak-theme"
  }
}
```

Running `npm run build` outputs `dist_keycloak/keycloak-theme.jar`. You can copy this JAR directly into `/opt/keycloak/providers/` on your Keycloak server.

## What Can Go Wrong

- **Interfering with `onSubmit`**: Preventing form submission via `event.preventDefault()` without manually submitting via hidden elements breaks Keycloak's session exchange. Always use standard form action targets.
- **Missing Resource Base URLs**: Assets referenced with hardcoded root paths (`/logo.png`) fail in production because Keycloak serves theme files from `/resources/<version>/login/<theme-name>/`. Always import static assets through bundler imports or `url.resourcesPath`.

## Summary

Keycloakify eliminates the friction of legacy FreeMarker templates by introducing TypeScript, React, and modern CSS tooling. Because authentication payloads are routed through `url.loginAction`, the core security perimeter of Keycloak remains untouched while frontend teams gain a modern development experience.

## Further Reading

- [Official Keycloakify Documentation](https://www.keycloakify.dev)
- [Keycloak Server Administration Guide](https://www.keycloak.org/docs/latest/server_admin/)
- [Vite Static Asset Handling](https://vitejs.dev/guide/assets.html)

Install Keycloakify in your React project to start replacing raw FreeMarker templates with type-safe authentication views.
