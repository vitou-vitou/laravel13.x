---
title: "How to Master Keycloak Context and Page Dispatching in Keycloakify"
published: true
description: "Understand the kcContext data contract and design a clean routing architecture for multi-step authentication pages in Keycloakify."
tags: "keycloak, keycloakify, typescript, architecture"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/69-keycloakify-kccontext-routing-architecture.md"
---

Authentication flows in Keycloak are non-linear state machines. A user might begin on a standard credentials screen, get prompted for a mandatory password reset, be redirected to register a WebAuthn token, and finally land on a terms-of-service confirmation page.

Keycloakify represents this dynamic workflow through a strongly typed object called `kcContext`. Understanding how to dispatch on `pageId` prevents unhandled routes and broken authentication states. Let's see how.

## The Problem: The Single Page Fallacy

Developers accustomed to client-side routers like React Router often attempt to manage Keycloak authentication via browser URL states (`/login`, `/register`). This approach fails because Keycloak retains server-side authority over the flow.

Keycloak invokes the browser at whatever step the server-side authenticator requires. It identifies the target screen using `kcContext.pageId`. Attempting to mount a single client-side route breaks whenever Keycloak redirects to an unexpected challenge, such as an expired temporary password.

## Type-Safe Context Definition

Keycloakify provides built-in typings for all core Keycloak FreeMarker templates (`login.ftl`, `register.ftl`, `login-reset-password.ftl`, `info.ftl`, etc.). You declare your supported context by extending the base contract:

`src/login/KcContext.ts:`
```typescript
import { createGetKcContext } from 'keycloakify/login';

export type KcContextExtension = {
    // Custom realm or theme properties passed from theme.properties
    themeVersion: string;
};

export type KcContextExtensionPerPage = {
    'login.ftl': {
        customBannerNotice?: string;
    };
    'login-reset-password.ftl': {
        minimumPasswordAgeDays?: number;
    };
};

export const { getKcContext } = createGetKcContext<
    KcContextExtension,
    KcContextExtensionPerPage
>({
    mockData: [
        {
            pageId: 'login.ftl',
            locale: { currentLanguageTag: 'en' },
            realm: { displayName: 'Production IAM' },
        },
    ],
});

export type KcContext = NonNullable<
    ReturnType<typeof getKcContext>['kcContext']
>;
```

This configuration guarantees compile-time validation for standard Keycloak properties and custom theme extensions.

## Designing the Master Page Dispatcher

Instead of relying on URL routers, implement a clean `switch` statement based on `kcContext.pageId`. Use React's lazy loading to keep bundle sizes minimal for users who only hit the primary login page.

`src/login/KcApp.tsx:`
```tsx
import { lazy, Suspense } from 'react';
import type { KcContext } from './KcContext';
import DefaultFallbackLayout from './components/DefaultFallbackLayout';

const Login = lazy(() => import('./pages/Login'));
const LoginResetPassword = lazy(() => import('./pages/LoginResetPassword'));
const Register = lazy(() => import('./pages/Register'));
const Info = lazy(() => import('./pages/Info'));
const ErrorPage = lazy(() => import('./pages/ErrorPage'));

export default function KcApp({ kcContext }: { kcContext: KcContext }) {
    return (
        <Suspense fallback={<div className="min-h-screen grid place-content-center text-slate-500">Loading flow...</div>}>
            {(() => {
                switch (kcContext.pageId) {
                    case 'login.ftl':
                        return <Login kcContext={kcContext} />;
                    case 'login-reset-password.ftl':
                        return <LoginResetPassword kcContext={kcContext} />;
                    case 'register.ftl':
                        return <Register kcContext={kcContext} />;
                    case 'info.ftl':
                        return <Info kcContext={kcContext} />;
                    case 'error.ftl':
                        return <ErrorPage kcContext={kcContext} />;
                    default:
                        // Fallback gracefully renders standard Keycloakify un-styled layout
                        return <DefaultFallbackLayout kcContext={kcContext} />;
                }
            })()}
        </Suspense>
    );
}
```

The `default` branch ensures that uncustomized pages—such as terms acceptance or user profile updates—continue to function safely rather than displaying a blank screen.

## Accessing Page-Specific Context Props

Narrowing `kcContext` per page gives each component access only to variables valid for its specific state:

`src/login/pages/LoginResetPassword.tsx:`
```tsx
import type { KcContext } from '../KcContext';

type Props = {
    kcContext: Extract<KcContext, { pageId: 'login-reset-password.ftl' }>;
};

export default function LoginResetPassword({ kcContext }: Props) {
    const { url, realm, messagesPerField } = kcContext;

    return (
        <div className="max-w-md mx-auto my-12 bg-white p-6 rounded-lg shadow-sm border border-slate-200">
            <h2 className="text-lg font-semibold text-slate-800 mb-2">Reset Password</h2>
            <p className="text-sm text-slate-600 mb-4">
                Enter your username or email address and we will send instructions to reset your account credentials on {realm.displayName}.
            </p>

            <form action={url.loginAction} method="post" className="space-y-4">
                <div>
                    <label className="block text-xs font-semibold uppercase text-slate-500 mb-1">
                        Username or Email
                    </label>
                    <input
                        type="text"
                        name="username"
                        defaultValue={kcContext.auth?.attemptedUsername ?? ''}
                        required
                        className="w-full border rounded px-3 py-2 text-sm text-slate-800"
                    />
                    {messagesPerField.existsError('username') && (
                        <p className="text-xs text-rose-600 mt-1">
                            {messagesPerField.getFirstError('username')}
                        </p>
                    )}
                </div>

                <div className="flex justify-between items-center pt-2">
                    <a href={url.loginUrl} className="text-xs text-blue-600 hover:underline">
                        Back to Login
                    </a>
                    <button
                        type="submit"
                        className="bg-blue-600 text-white px-4 py-2 text-sm rounded hover:bg-blue-700 transition"
                    >
                        Send Reset Email
                    </button>
                </div>
            </form>
        </div>
    );
}
```

Using `Extract<KcContext, { pageId: '...' }>` prevents runtime `undefined` errors when inspecting auth or url properties.

## What Can Go Wrong

- **Ignoring Edge Case Pages**: Keycloak can trigger unexpected screens such as `login-update-password.ftl` or `login-page-expired.ftl`. If your dispatcher lacks a fallback, users encounter a blank white screen during expired session recoveries.
- **Manipulating History**: Calling `window.history.pushState` during authentication confuses Keycloak's server-side session cookies and can break back-button behavior. Let Keycloak drive navigation.

## Summary

`kcContext` acts as the server-authoritative bridge between Keycloak's state engine and your UI components. Using TypeScript discriminant unions on `pageId` creates a predictable, type-safe architecture that cleanly handles multi-step authentication challenges.

## Further Reading

- [Keycloakify Context Reference](https://docs.keycloakify.dev/kccontext)
- [Keycloak Server Flow Architecture](https://www.keycloak.org/docs/latest/server_development/#_auth_spi)
- [TypeScript Discriminated Unions](https://www.typescriptlang.org/docs/handbook/2/narrowing.html#discriminated-unions)

Structure your Keycloakify dispatcher using strict page unions to guarantee robust authentication flows across every user state.
