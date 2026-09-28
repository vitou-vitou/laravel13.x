---
title: "How to Configure Local Development and Mocking for Keycloakify"
published: true
description: "Master local testing workflows in Keycloakify using Storybook and mock kcContext states without running a full Keycloak server instance."
tags: "keycloak, keycloakify, react, testing"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/67-keycloakify-local-dev-mock-context.md"
---

Iterating on Keycloak authentication themes against a live Java container is slow. Rebuilding JAR packages, redeploying container volumes, and manually triggering error states like "Account temporarily disabled" adds unnecessary development friction.

Keycloakify features a mock context framework and Storybook integration that simulates every possible Keycloak authentication state directly in Vite. Let's see how.

## How Mock Context Works

In production, Keycloak evaluates FreeMarker templates server-side and injects `window.kcContext`. In local development, Keycloakify's runtime replaces this injection with a mock JavaScript object tailored to the current route.

You can inspect and customize this mock baseline inside your project configuration.

## Step 1: Define Custom Mock States

Configure realistic test scenarios by extending the default mock context:

src/login/kcContextMocks.ts:
```typescript
import { createGetKcContextMock } from 'keycloakify/login/KcContext';
import type { KcContextExtension, KcContextExtensionPerPage } from './KcContext';

export const { getKcContextMock } = createGetKcContextMock({
    kcContextExtension: {
        themeVersion: '2.4.0',
    },
    kcContextExtensionPerPage: {
        'login.ftl': {
            realm: {
                displayName: 'Acme Cloud Platform',
                registrationEmailAsUsername: true,
                resetPasswordAllowed: true,
            },
            messagesPerField: {
                existsError: (field: string) => field === 'password',
                getFirstError: (field: string) =>
                    field === 'password' ? 'Invalid credentials provided. 2 attempts remaining.' : undefined,
            },
        },
    },
});
```

This mock exposes a realistic password failure state without touching a live authentication database.

## Step 2: Render Component with Custom Mock Props

Mount your component in a Storybook file or Vite playground using the generated mock:

src/login/pages/Login.stories.tsx:
```tsx
import type { Meta, StoryObj } from '@storybook/react';
import { createKcPageStory } from 'keycloakify/login';
import type { KcContext } from '../KcContext';

const { KcPageStory } = createKcPageStory<KcContext>({
    pageId: 'login.ftl',
});

const meta: Meta = {
    title: 'Login / Default Flow',
    component: KcPageStory,
};

export default meta;
type Story = StoryObj;

export const WithPasswordError: Story = {
    render: () => (
        <KcPageStory
            kcContext={{
                messagesPerField: {
                    existsError: () => true,
                    getFirstError: () => 'Your account has been locked due to excessive failed attempts.',
                },
            }}
        />
    ),
};
```

Loading this story in Vite or Storybook lets you verify layout shifts, error colors, and typography instantly.

## What Can Go Wrong

- **Type Drift:** Customizing mock schemas without updating `KcContextExtensionPerPage` leads to silent undefined property bugs when deployed to Keycloak.
- **Form Submission Traps:** Clicking submit in a mock environment triggers a simulated POST to `url.loginAction`. Prevent confusing page reloads in Storybook by mocking the form event handler during local stories.

## Summary

Decoupling theme development from Keycloak's Java container cuts iteration loops from minutes to milliseconds. Storybook and Keycloakify's mock context guarantee that all edge-case error banners look polished before packaging.

## Further Reading

- [Keycloakify Local Testing Guide](https://www.keycloakify.dev/documentation)
- [Storybook for React Documentation](https://storybook.js.org/docs/react/get-started/introduction)
- [Keycloak Authentication Flows](https://www.keycloak.org/docs/latest/server_admin/#_authentication_flows)
