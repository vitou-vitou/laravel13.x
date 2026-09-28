---
title: "How to Build Custom Keycloak Account Consoles in Keycloakify"
published: true
description: "Customize Keycloak account management pages, session terminators, and profile editors using Keycloakify Account Theme mode."
tags: "keycloak, keycloakify, account-console, user-management"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/75-keycloakify-account-theme-customization.md"
---

Most theme projects focus exclusively on the login screen (`login-theme`). However, once users log in, accessing their profile, changing passwords, managing sessions, and enabling two-factor authentication takes them to Keycloak's default Account Console. When that console looks completely different from the login experience, users get confused and brand trust suffers.

Keycloakify supports building custom Account Themes alongside Login Themes using the same React, TypeScript, and Tailwind foundation. Let's see how.

## Architecture: Login Theme vs. Account Theme

Keycloak handles login and account management through two distinct theme categories:

- **Login Theme (`/login`)**: Unauthenticated flows (login, registration, OTP challenges, password reset) driven by server-side FreeMarker page templates.
- **Account Theme (`/account`)**: Post-authentication dashboard (personal info, session revocation, linked identity providers, credential management).

In modern Keycloak versions, the default account console runs as a client-side Single Page Application (SPA). Keycloakify enables customizing this interface through server-rendered templates (`account.ftl`, `password.ftl`, `sessions.ftl`) compiled into the same theme JAR.

## Step 1: Enable Account Theme in Keycloakify

Configure your project to build both login and account themes in your build configuration:

`package.json:`
```json
{
  "name": "enterprise-keycloak-theme",
  "keycloakify": {
    "themeType": ["login", "account"]
  }
}
```

This configuration instructs Keycloakify to build bundles for both theme types and package them into the final JAR.

## Step 2: Establish the Account Page Dispatcher

Account templates receive their context through an account-specific variant of `kcContext`:

`src/account/KcApp.tsx:`
```tsx
import { lazy, Suspense } from 'react';
import type { KcContext } from './KcContext';

const Account = lazy(() => import('./pages/Account'));
const Password = lazy(() => import('./pages/Password'));
const Sessions = lazy(() => import('./pages/Sessions'));

export default function KcApp({ kcContext }: { kcContext: KcContext }) {
    return (
        <Suspense fallback={<div className="p-8 text-center text-slate-500">Loading Account Console...</div>}>
            {(() => {
                switch (kcContext.pageId) {
                    case 'account.ftl':
                        return <Account kcContext={kcContext} />;
                    case 'password.ftl':
                        return <Password kcContext={kcContext} />;
                    case 'sessions.ftl':
                        return <Sessions kcContext={kcContext} />;
                    default:
                        return <div className="p-6">Page not customized: {kcContext.pageId}</div>;
                }
            })()}
        </Suspense>
    );
}
```

This dispatcher matches the requested account view and renders the appropriate management panel.

## Step 3: Implement an Active Session Revocation Panel

One of the most critical security features in the account console is the ability to view active devices and revoke unauthorized sessions:

`src/account/pages/Sessions.tsx:`
```tsx
import type { KcContext } from '../KcContext';

type Props = {
    kcContext: Extract<KcContext, { pageId: 'sessions.ftl' }>;
};

export default function Sessions({ kcContext }: Props) {
    const { url, sessions, stateChecker } = kcContext;

    return (
        <div className="max-w-4xl mx-auto my-8 p-6 bg-white rounded-xl shadow-sm border border-slate-200">
            <div className="flex justify-between items-center mb-6">
                <div>
                    <h2 className="text-xl font-bold text-slate-800">Active Login Sessions</h2>
                    <p className="text-xs text-slate-500 mt-1">
                        Review and terminate your active corporate sessions across all devices.
                    </p>
                </div>

                <form action={url.sessionsUrl} method="post">
                    <input type="hidden" name="stateChecker" value={stateChecker} />
                    <button
                        type="submit"
                        name="action"
                        value="logoutAll"
                        className="px-3 py-1.5 text-xs font-semibold text-rose-600 border border-rose-200 bg-rose-50 hover:bg-rose-100 rounded transition"
                    >
                        Sign Out of All Devices
                    </button>
                </form>
            </div>

            <div className="divide-y divide-slate-100 border border-slate-100 rounded-lg overflow-hidden">
                {sessions.sessions.map((session) => (
                    <div key={session.id} className="p-4 flex justify-between items-center bg-slate-50/50">
                        <div>
                            <div className="flex items-center gap-2">
                                <span className="font-semibold text-sm text-slate-800">{session.ipAddress}</span>
                                {session.current && (
                                    <span className="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded">
                                        Current Device
                                    </span>
                                )}
                            </div>
                            <p className="text-xs text-slate-500 mt-1">
                                Started: {session.started} &bull; Last access: {session.lastAccess}
                            </p>
                        </div>

                        {!session.current && (
                            <form action={url.sessionsUrl} method="post">
                                <input type="hidden" name="stateChecker" value={stateChecker} />
                                <input type="hidden" name="action" value="logout" />
                                <input type="hidden" name="session" value={session.id} />
                                <button
                                    type="submit"
                                    className="text-xs text-slate-600 hover:text-rose-600 underline"
                                >
                                    Revoke
                                </button>
                            </form>
                        )}
                    </div>
                ))}
            </div>
        </div>
    );
}
```

Submitting `stateChecker` ensures CSRF protection is maintained when revoking session tokens.

## What Can Go Wrong

- **Missing `stateChecker` Parameter**: Keycloak requires a valid `stateChecker` token on all state-altering account actions. Omitting this hidden input returns an HTTP 403 Forbidden error.
- **Account Console v2 vs. v3 Divergence**: In Keycloak 24+, the official account console uses a client-side React architecture that operates differently from server-side templates. If you replace the account theme with Keycloakify, set the Account Theme to your custom package in the realm settings to override the default SPA console.

## Summary

Extending Keycloakify to your Account Theme guarantees a consistent, branded experience before and after authentication. By supporting profile editing and session revocation views, users enjoy a cohesive security interface throughout their session lifecycle.

## Further Reading

- [Keycloak Account Console Documentation](https://www.keycloak.org/docs/latest/server_admin/#_account_service)
- [Keycloakify Account Theme Guide](https://docs.keycloakify.dev/account-theme)
- [Session Management Security (OWASP)](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)

Add an Account Theme configuration to your Keycloakify project to deliver a seamless user experience across the full authentication lifecycle.
