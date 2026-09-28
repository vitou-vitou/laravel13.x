---
title: "How to Migrate Legacy FreeMarker Themes to Keycloakify React"
published: true
description: "A step-by-step migration blueprint to convert legacy FreeMarker .ftl templates and jQuery assets into clean, type-safe React Keycloakify components."
tags: "keycloak, keycloakify, migration, freemarker, react"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/78-keycloakify-migrating-legacy-freemarker.md"
---

Many enterprises run custom Keycloak login themes built years ago with raw FreeMarker templates, custom CSS files, and vanilla JavaScript or jQuery plugins. These themes often become unmaintainable as original authors leave, dependencies age, and browser compatibility drifts.

Migrating to Keycloakify provides a path to modern React components, TypeScript type safety, and clean asset pipelines without disrupting active user authentication. Let's see how.

## The Strategy: Strangler Migration for Auth Screens

Attempting a full rewrite of every FreeMarker file at once introduces significant release risk. A typical theme contains dozens of screens—including account verification, error states, and Terms of Service prompts.

The recommended approach is an incremental strangler migration:

1. **Extract and audit**: Identify the core FreeMarker templates actively used by the realm.
2. **Setup Keycloakify with fallbacks**: Delegate unmigrated screens to standard base templates.
3. **Migrate high-traffic pages**: Rebuild the top three views (`login.ftl`, `login-reset-password.ftl`, `register.ftl`) in React.
4. **Decommission legacy assets**: Retire old jQuery plugins, unstructured CSS files, and raw `.ftl` files.

## Step 1: Audit the Extracted FreeMarker Theme

Locate your legacy theme directory (often stored as an extracted archive like `extracted-theme/`):

`theme/login-theme/login/theme.properties:`
```properties
parent=keycloak
import=common/keycloak
styles=css/styles.css
scripts=js/jquery.min.js js/main.js
locales=en,km
```

Next, check `login.ftl` for custom form inputs or hidden fields required by backend authenticators:

`theme/login-theme/login/login.ftl:`
```html
<#-- Legacy snippet extracting corporate employee ID -->
<div class="form-group">
    <label for="employeeId">${msg("employeeIdLabel")}</label>
    <input type="text" id="employeeId" name="employeeId" class="form-control" />
</div>
```

Note every custom input name (`employeeId`) and form action attribute. These must be preserved in the new React components to avoid breaking backend expectations.

## Step 2: Establish the Migration Skeleton in React

Set up a default fallback in your Keycloakify dispatcher so unmigrated screens continue to render using Keycloakify's standard layouts:

`src/login/KcApp.tsx:`
```tsx
import { lazy, Suspense } from 'react';
import type { KcContext } from './KcContext';
import DefaultFallbackLayout from './components/DefaultFallbackLayout';

// Migrated screens
const Login = lazy(() => import('./pages/Login'));
const LoginResetPassword = lazy(() => import('./pages/LoginResetPassword'));

export default function KcApp({ kcContext }: { kcContext: KcContext }) {
    return (
        <Suspense fallback={<div className="p-8 text-center">Loading...</div>}>
            {(() => {
                switch (kcContext.pageId) {
                    case 'login.ftl':
                        return <Login kcContext={kcContext} />;
                    case 'login-reset-password.ftl':
                        return <LoginResetPassword kcContext={kcContext} />;
                    default:
                        // Unmigrated pages safely fall back to the base layout
                        return <DefaultFallbackLayout kcContext={kcContext} />;
                }
            })()}
        </Suspense>
    );
}
```

This ensures that even if you have only migrated the login screen, features like multi-factor authentication or password update prompts remain functional.

## Step 3: Convert FreeMarker Directives to React

Map legacy FreeMarker template logic directly to modern React patterns:

| FreeMarker Expression | React / Keycloakify Equivalent |
|---|---|
| `<#if messagesPerField.existsError('username')>` | `messagesPerField.existsError('username')` |
| `${msg("loginTitle")}` | `msg('loginTitle')` (from `useI18n`) |
| `${url.loginAction}` | `kcContext.url.loginAction` |
| `<#list realm.identityProviders as p>` | `realm.identityProviders?.map(p => ...)` |
| `<input value="${(login.username!'')}">` | `defaultValue={kcContext.login?.username ?? ''}` |

Here is the migrated `Login.tsx` component preserving legacy form parameters:

`src/login/pages/Login.tsx:`
```tsx
import type { KcContext } from '../KcContext';
import { useI18n } from '../i18n';

type Props = {
    kcContext: Extract<KcContext, { pageId: 'login.ftl' }>;
};

export default function Login({ kcContext }: Props) {
    const { url, messagesPerField } = kcContext;
    const { msg } = useI18n({ kcContext });

    return (
        <div className="max-w-md mx-auto my-12 p-8 bg-white rounded-lg shadow-sm border border-slate-200">
            <h1 className="text-xl font-bold text-slate-900 mb-6">{msg('loginTitle')}</h1>

            <form action={url.loginAction} method="post" className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">
                        {msg('usernameOrEmail')}
                    </label>
                    <input
                        type="text"
                        name="username"
                        defaultValue={kcContext.login?.username ?? ''}
                        required
                        className="w-full px-3 py-2 border rounded-md text-sm border-slate-300"
                    />
                </div>

                {/* Migrated custom field from legacy FreeMarker template */}
                <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">
                        Corporate Employee ID
                    </label>
                    <input
                        type="text"
                        name="employeeId"
                        className="w-full px-3 py-2 border rounded-md text-sm border-slate-300"
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">
                        {msg('password')}
                    </label>
                    <input
                        type="password"
                        name="password"
                        required
                        className="w-full px-3 py-2 border rounded-md text-sm border-slate-300"
                    />
                    {messagesPerField.existsError('password') && (
                        <p className="text-xs text-red-600 mt-1">
                            {messagesPerField.getFirstError('password')}
                        </p>
                    )}
                </div>

                <button
                    type="submit"
                    className="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-sm transition"
                >
                    {msg('doLogIn')}
                </button>
            </form>
        </div>
    );
}
```

The resulting component retains the exact form field signatures required by the backend while shedding legacy jQuery code and FreeMarker tags.

## Step 4: Validate in Staging

Before switching production traffic to the new theme:

1. Build the new theme JAR: `npm run build`.
2. Deploy the JAR to a staging Keycloak cluster.
3. Test the full authentication lifecycle: standard login, incorrect credentials, password reset, and session timeouts.
4. Verify that unmigrated fallback pages display legible layouts and accept input correctly.

## What Can Go Wrong

- **Missing Hidden Input Fields**: Legacy templates often contain hidden elements like `<input type="hidden" name="credentialId" value="...">` used by custom authenticator extensions. Omitting these fields can cause authentication challenges to fail silently.
- **Incompatible Message Key Formats**: Legacy `.properties` files that use dot notation (`login.label.username`) may need to be flattened or adapted when imported into TypeScript translation dictionaries.

## Summary

Migrating from legacy FreeMarker templates to Keycloakify eliminates template maintenance debt and equips frontend teams with a modern React workflow. By incrementally migrating core pages and using standard fallbacks for secondary views, teams modernize their authentication UI with minimal risk.

## Further Reading

- [Keycloak FreeMarker Migration Guide](https://www.keycloak.org/docs/latest/server_development/#_themes)
- [Keycloakify Migration Playbook](https://docs.keycloakify.dev/migration)
- [Strangler Fig Application Modernization Pattern](https://martinfowler.com/bliki/StranglerFigApplication.html)

Audit your existing FreeMarker themes to begin a phased migration toward type-safe React Keycloakify components.
