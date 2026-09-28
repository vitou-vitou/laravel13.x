---
title: "How to Configure Local Development and Fast Mocking in Keycloakify"
published: true
description: "Accelerate frontend Keycloak theme iteration using Vite hot module replacement, Storybook stories, and isolated mock context states."
tags: "keycloak, keycloakify, vite, storybook, testing"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/73-keycloakify-storybook-local-mocking.md"
---

Developing custom Keycloak themes traditionally involves a slow feedback loop. Making a minor CSS change often requires compiling a Java archive, restarting or reloading a local Keycloak container, navigating to an OAuth client, and triggering the specific authentication state manually.

Keycloakify decouples theme development from the Keycloak server by using Vite and Storybook to simulate authentication states with full Hot Module Replacement (HMR). Let's see how.

## The Problem: The Slow Server Loop

A complete Keycloak theme must account for dozens of edge-case states:

- Expired password prompts with strict complexity rules
- Account lockout warnings following failed attempts
- WebAuthn security key prompts
- Multi-factor SMS timeouts

Triggering each of these flows against a live Keycloak server requires administrative manipulation of database tables, rate limits, and test users. In contrast, local mock environments allow developers to jump directly to any authentication state instantly.

## Architecture: Mock Context Injection

When running in development mode (`npm run dev`), Keycloakify checks for the absence of `window.kcContext`. Instead of failing, it substitutes a configurable mock object tailored to the route or Storybook story being viewed.

## Step 1: Define Scenarios with KcContext Mock Extensions

You can define realistic data states for any screen in your mock configuration:

`src/login/kcContextMocks.ts:`
```typescript
import { createGetKcContextMock } from 'keycloakify/login/KcContext';
import type { KcContextExtension, KcContextExtensionPerPage } from './KcContext';

export const { getKcContextMock } = createGetKcContextMock({
    kcContextExtension: {
        themeVersion: '1.4.0',
    },
    kcContextExtensionPerPage: {
        'login.ftl': {
            realm: {
                displayName: 'Acme Financial IAM',
                resetPasswordAllowed: true,
                rememberMe: true,
            },
            messagesPerField: {
                existsError: (field: string) => field === 'password',
                getFirstError: (field: string) =>
                    field === 'password'
                        ? 'Invalid credentials. 2 attempts remaining before lockout.'
                        : undefined,
            },
        },
        'login-reset-password.ftl': {
            auth: {
                attemptedUsername: 'employee@acme.corp',
            },
        },
    },
});
```

These mock states allow you to test error styling, warning banners, and edge cases locally in milliseconds.

## Step 2: Set Up Storybook Stories for Edge Cases

Storybook provides a structured canvas for cataloging authentication screens across different states and languages:

`.storybook/preview.tsx:`
```tsx
import type { Preview } from '@storybook/react';
import '../src/main.css';

const preview: Preview = {
    parameters: {
        actions: { argTypesRegex: '^on[A-Z].*' },
        controls: {
            matchers: {
                color: /(background|color)$/i,
                date: /Date$/,
            },
        },
    },
};

export default preview;
```

Now create component stories for your standard login view and error states:

`src/login/pages/Login.stories.tsx:`
```tsx
import type { Meta, StoryObj } from '@storybook/react';
import { createKcPageStory } from 'keycloakify/login';
import type { KcContext } from '../KcContext';

const { KcPageStory } = createKcPageStory<KcContext>({
    pageId: 'login.ftl',
});

const meta: Meta<typeof KcPageStory> = {
    title: 'Authentication/Login Screen',
    component: KcPageStory,
};

export default meta;
type Story = StoryObj<typeof KcPageStory>;

export const Default: Story = {
    args: {
        kcContext: {
            realm: { displayName: 'Enterprise Portal' },
        },
    },
};

export const AccountLockedWarning: Story = {
    args: {
        kcContext: {
            realm: { displayName: 'Enterprise Portal' },
            message: {
                type: 'error',
                summary: 'Your account has been temporarily locked due to excessive failed attempts.',
            },
        },
    },
};

export const PasswordExpired: Story = {
    args: {
        kcContext: {
            login: { username: 'john.doe@enterprise.com' },
            message: {
                type: 'warning',
                summary: 'Your temporary password has expired. Please authenticate to reset.',
            },
        },
    },
};
```

Developers can toggle between these states in Storybook without waiting for container deployments or server reboots.

## Step 3: Run the Vite Standalone Server

For standard page previewing in your browser, launch Vite directly:

`package.json:`
```json
{
  "scripts": {
    "dev": "vite",
    "storybook": "storybook dev -p 6006"
  }
}
```

Visiting `http://localhost:5173` renders your theme with instant hot reload. Changes to Tailwind classes, form inputs, or typography reflect immediately.

## What Can Go Wrong

- **Testing Only in Mock Environments**: While mock states speed up UI development, form actions and redirects still need verification against Keycloak's real session handler. Always run a smoke test against a real Keycloak instance before deploying to production.
- **Leaking Mock Data into Builds**: Keycloakify strips mock contexts during the production packaging step (`npm run build`). Avoid writing custom fallbacks like `kcContext = mockData || window.kcContext` in application code; use Keycloakify's built-in mock harness instead.

## Summary

Decoupling theme development from Keycloak's server cycle transforms the frontend workflow. By using Vite for local development and Storybook for UI state management, teams can build and verify complex authentication interfaces rapidly and reliably.

## Further Reading

- [Keycloakify Local Testing Guide](https://docs.keycloakify.dev/storybook)
- [Storybook Documentation](https://storybook.js.org/docs)
- [Vite Fast Refresh Overview](https://vitejs.dev/guide/features.html#hot-module-replacement)

Configure mock contexts and Storybook in your Keycloakify project to speed up your theme development lifecycle.
