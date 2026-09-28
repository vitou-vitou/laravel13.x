---
title: "How to Implement Multi-Language Localization in Keycloakify"
published: true
description: "Support internationalized user interfaces in Keycloakify themes using i18n message catalogs and Keycloak realm language toggles."
tags: "keycloak, keycloakify, i18n, localization"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/71-keycloakify-i18n-localization.md"
---

Enterprise authentication platforms frequently serve international users across multiple languages. Keycloak natively supports realm-level localization, but adapting raw FreeMarker message bundles (`messages_en.properties`, `messages_fr.properties`) often leads to missing translation keys and formatting inconsistencies.

Keycloakify introduces a type-safe internationalization (i18n) layer that synchronizes seamlessly with Keycloak's server-side language preferences. Let's see how.

## How Keycloak Handles Localization

When internationalization is enabled in the Keycloak admin console, Keycloak detects the user's preferred language via browser headers (`Accept-Language`), user account settings, or an explicit query parameter (`?ui_locales=fr`).

Keycloak then populates `kcContext.locale` with:

- `currentLanguageTag`: The active locale (e.g., `'en'`, `'fr'`, `'km'`).
- `supported`: An array of available languages configured in the realm.

In traditional themes, missing keys fall back to hardcoded English strings or raw property tokens. Keycloakify catches these gaps at compile time using TypeScript.

## Step 1: Define Type-Safe Message Dictionaries

Create message dictionaries for your supported languages:

`src/login/i18n/messages_en.ts:`
```typescript
export const messages_en = {
    loginTitle: 'Sign in to your account',
    usernameOrEmail: 'Username or Corporate Email',
    password: 'Password',
    signInButton: 'Sign In',
    forgotPassword: 'Forgot your password?',
    otpVerificationPrompt: 'Enter the 6-digit code sent to your device',
    invalidCredentials: 'The username or password you entered is incorrect.',
};
```

Define the corresponding translations for additional locales:

`src/login/i18n/messages_fr.ts:`
```typescript
import type { messages_en } from './messages_en';

export const messages_fr: typeof messages_en = {
    loginTitle: 'Connectez-vous à votre compte',
    usernameOrEmail: 'Identifiant ou e-mail d’entreprise',
    password: 'Mot de passe',
    signInButton: 'Se connecter',
    forgotPassword: 'Mot de passe oublié ?',
    otpVerificationPrompt: 'Entrez le code à 6 chiffres envoyé à votre appareil',
    invalidCredentials: 'L’identifiant ou le mot de passe est incorrect.',
};
```

Typing `messages_fr` as `typeof messages_en` forces TypeScript to fail the build if any translation key is missing or mistyped.

## Step 2: Initialize Keycloakify i18n

Connect your custom message catalogs to Keycloakify's internal i18n provider:

`src/login/i18n/index.ts:`
```typescript
import { createUseI18n } from 'keycloakify/login';
import { messages_en } from './messages_en';
import { messages_fr } from './messages_fr';

export const { useI18n } = createUseI18n({
    en: {
        ...messages_en,
    },
    fr: {
        ...messages_fr,
    },
});

export type I18n = ReturnType<typeof useI18n>;
```

This hook automatically aligns with `kcContext.locale.currentLanguageTag` to return the matching translation dictionary.

## Step 3: Integrate Language Switcher and Translated Labels

Incorporate the `msg` function and language switcher directly into your login template:

`src/login/pages/Login.tsx:`
```tsx
import type { KcContext } from '../KcContext';
import { useI18n } from '../i18n';

export default function Login({ kcContext }: { kcContext: Extract<KcContext, { pageId: 'login.ftl' }> }) {
    const { url, locale } = kcContext;
    const { msg } = useI18n({ kcContext });

    return (
        <div className="max-w-md mx-auto my-12 bg-white p-8 rounded-xl shadow-sm border border-slate-200">
            {/* Language Switcher */}
            {locale && locale.supported.length > 1 && (
                <div className="flex justify-end gap-2 mb-6">
                    {locale.supported.map((lang) => (
                        <a
                            key={lang.languageTag}
                            href={lang.url}
                            className={`text-xs px-2 py-1 rounded transition ${
                                lang.languageTag === locale.currentLanguageTag
                                    ? 'bg-slate-900 text-white font-semibold'
                                    : 'text-slate-600 hover:bg-slate-100'
                            }`}
                        >
                            {lang.label}
                        </a>
                    ))}
                </div>
            )}

            <h1 className="text-xl font-bold text-slate-800 mb-6 text-center">
                {msg('loginTitle')}
            </h1>

            <form action={url.loginAction} method="post" className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-slate-700 mb-1">
                        {msg('usernameOrEmail')}
                    </label>
                    <input
                        type="text"
                        name="username"
                        required
                        className="w-full px-3 py-2 border border-slate-300 rounded-md text-sm"
                    />
                </div>

                <div>
                    <div className="flex justify-between items-center mb-1">
                        <label className="block text-sm font-medium text-slate-700">
                            {msg('password')}
                        </label>
                        <a href={url.loginResetCredentialsUrl} className="text-xs text-blue-600 hover:underline">
                            {msg('forgotPassword')}
                        </a>
                    </div>
                    <input
                        type="password"
                        name="password"
                        required
                        className="w-full px-3 py-2 border border-slate-300 rounded-md text-sm"
                    />
                </div>

                <button
                    type="submit"
                    className="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-sm transition"
                >
                    {msg('signInButton')}
                </button>
            </form>
        </div>
    );
}
```

When users click an alternate language link, Keycloak refreshes the page with the updated locale query parameter, ensuring form inputs and server validation messages match the chosen language.

## What Can Go Wrong

- **Client-Side State vs. Server State**: Trying to switch locales via React state (`useState`) without navigating to `lang.url` leaves Keycloak's server-side session in the original language. Always use the server-provided URLs in `locale.supported`.
- **FreeMarker Overrides**: If `messages_en.properties` exists inside your theme's static folder, Keycloak may prioritize those keys over compiled values. Maintain your translations strictly inside your TypeScript dictionaries.

## Summary

Keycloakify's i18n utilities bridge TypeScript dictionaries with Keycloak's server-driven localization engine. This setup guarantees type safety, eliminates missing keys at compile time, and gives users a seamless multi-language authentication experience.

## Further Reading

- [Keycloak Internationalization Admin Guide](https://www.keycloak.org/docs/latest/server_admin/#_internationalization)
- [Keycloakify i18n Documentation](https://docs.keycloakify.dev/i18n)
- [Standard BCP 47 Language Tags](https://tools.ietf.org/html/bcp47)

Implement TypeScript-backed message catalogs in your Keycloakify project to deliver reliable localized authentication interfaces.
