---
title: "How to Style Keycloak Login Themes with Tailwind CSS and Keycloakify"
published: true
description: "Configure Tailwind CSS and PostCSS inside Keycloakify to build branded login layouts without CSS asset conflicts in production Keycloak clusters."
tags: "keycloak, keycloakify, tailwindcss, css"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/70-keycloakify-tailwind-styling-theme.md"
---

Styling traditional Keycloak templates requires writing legacy CSS stylesheets referenced through `theme.properties`. These stylesheets lack modern scoping, utility classes, and variable token integration, making consistent enterprise branding difficult to maintain across authentication pages.

Integrating Tailwind CSS with Keycloakify provides modern utility-first styling while compiling cleanly into Keycloak's static resource directory. Let's see how.

## The Challenge: Isolated Keycloak Assets

In production environments, Keycloak serves static assets from versioned paths such as `/resources/<version>/login/<theme-name>/`. If your CSS bundler relies on absolute URL paths like `/assets/main.css`, Keycloak will fail to locate your assets when mounted behind reverse proxies or custom realm prefixes.

Keycloakify solves this by rewriting asset URLs during the JAR packaging step, mapping Tailwind's output directly into Keycloak's resource directory structure.

## Step 1: Configure Tailwind CSS and PostCSS

Initialize Tailwind CSS inside your Keycloakify project using standard PostCSS integration:

`postcss.config.js:`
```javascript
export default {
    plugins: {
        tailwindcss: {},
        autoprefixer: {},
    },
};
```

Ensure your `tailwind.config.js` monitors all Keycloakify page components and layout wrappers:

`tailwind.config.js:`
```javascript
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './index.html',
        './src/**/*.{js,ts,jsx,tsx}',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#f0f9ff',
                    500: '#0284c7',
                    600: '#0369a1',
                    700: '#075985',
                },
            },
        },
    },
    plugins: [],
};
```

Import your Tailwind layers in your primary CSS entry point:

`src/main.css:`
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom authentication layout utilities */
.kc-login-card {
    @apply w-full max-w-md bg-white border border-slate-200 rounded-xl shadow-lg p-8;
}
```

## Step 2: Build a Shared Authentication Card Template

Most authentication screens (login, password reset, multi-factor verification) share identical branding, backgrounds, and logo headers. Centralize this structure inside a reusable template component:

`src/login/Template.tsx:`
```tsx
import type { ReactNode } from 'react';
import type { KcContext } from './KcContext';

type TemplateProps = {
    kcContext: KcContext;
    children: ReactNode;
    headerNode?: ReactNode;
};

export default function Template({ kcContext, children, headerNode }: TemplateProps) {
    const { realm } = kcContext;

    return (
        <div className="min-h-screen w-full flex flex-col justify-center items-center bg-slate-50 px-4 py-12 sm:px-6 lg:px-8">
            <div className="kc-login-card">
                <div className="text-center mb-6">
                    <div className="inline-flex items-center justify-center h-12 w-12 rounded-full bg-brand-50 text-brand-600 mb-3">
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h1 className="text-2xl font-semibold tracking-tight text-slate-900">
                        {headerNode || realm.displayName || 'Identity Portal'}
                    </h1>
                </div>

                {children}

                <div className="mt-6 border-t border-slate-100 pt-4 text-center">
                    <p className="text-xs text-slate-400">
                        Protected by Keycloak IAM &bull; Secure Authentication
                    </p>
                </div>
            </div>
        </div>
    );
}
```

By wrapping every page in this template, layout modifications automatically cascade across login, registration, and OTP verification flows.

## Step 3: Implement Branded Form Controls

Now assemble a cohesive login component using your custom Tailwind tokens:

`src/login/pages/Login.tsx:`
```tsx
import type { KcContext } from '../KcContext';
import Template from '../Template';

export default function Login({ kcContext }: { kcContext: Extract<KcContext, { pageId: 'login.ftl' }> }) {
    const { url, messagesPerField } = kcContext;

    return (
        <Template kcContext={kcContext} headerNode="Welcome Back">
            <form action={url.loginAction} method="post" className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">
                        Corporate ID or Email
                    </label>
                    <input
                        type="text"
                        name="username"
                        defaultValue={kcContext.login?.username ?? ''}
                        required
                        className="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition text-sm"
                        placeholder="user@enterprise.com"
                    />
                    {messagesPerField.existsError('username') && (
                        <p className="text-xs text-red-600 mt-1 font-medium">
                            {messagesPerField.getFirstError('username')}
                        </p>
                    )}
                </div>

                <div>
                    <div className="flex justify-between items-center mb-1">
                        <label className="block text-sm font-medium text-slate-700">Password</label>
                        {kcContext.realm.resetPasswordAllowed && (
                            <a href={url.loginResetCredentialsUrl} className="text-xs text-brand-600 hover:text-brand-700">
                                Forgot password?
                            </a>
                        )}
                    </div>
                    <input
                        type="password"
                        name="password"
                        required
                        className="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-800 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition text-sm"
                    />
                </div>

                <button
                    type="submit"
                    className="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg shadow-sm hover:shadow transition text-sm"
                >
                    Authenticate
                </button>
            </form>
        </Template>
    );
}
```

This implementation adheres to enterprise visual design requirements while maintaining Keycloak's server-driven authentication contracts.

## What Can Go Wrong

- **Purged Utility Classes**: If your `tailwind.config.js` omits `.ftl` or `.tsx` paths, Tailwind purges needed styles during the production build. Ensure your content array covers `./src/**/*.{ts,tsx}`.
- **Unscoped Global CSS**: Adding root styles to `body` or `html` can conflict with embedded Keycloak account consoles. Keep your styling scoped to container wrappers.

## Summary

Combining Tailwind CSS with Keycloakify provides modern styling workflows for Keycloak themes. By wrapping custom forms in a reusable layout template, enterprise applications achieve visual polish and brand consistency across every authentication state.

## Further Reading

- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Keycloakify CSS and Assets Guide](https://docs.keycloakify.dev/css-and-assets)
- [PostCSS Setup Configuration](https://postcss.org/)

Adopt Tailwind CSS inside your Keycloakify project to build maintainable, responsive theme interfaces.
