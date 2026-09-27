# Keycloakify Custom Themes: Build Keycloak Login Pages Without Breaking the Auth Flow

You are tasked with customizing your company's Keycloak login screen, and you open Keycloak's default FreeMarker templates: raw `.ftl` files written in archaic markup, with zero TypeScript support and painful local iteration. A frontend engineer attempts to fix this by building a standalone React app that submits credentials over an ad-hoc REST endpoint. Within days, you lose Keycloak's multi-factor authentication, broken session cookies cause redirect loops, and password resets fail silently.

Keycloakify bridges modern React and Keycloak's authentication engine. It allows you to build custom login, registration, and account pages using React, TypeScript, and Tailwind CSS, then compiles them directly into a standard Keycloak theme JAR. If you understand how Keycloak passes context into your templates, you can build branded authentication screens without breaking Keycloak's security lifecycle.

## Keycloak Is Not an SPA: Understand kcContext

The single most important concept to grasp is that a Keycloakify theme is not a client-side Single Page Application. Keycloak renders pages server-side during the authentication flow, injecting data into a global JavaScript variable called `kcContext`.

Your React components receive this `kcContext` as props, which contains:

- `url.loginAction`: The exact POST endpoint where credentials must be submitted.
- `messagesPerField`: Server-side validation errors (e.g., "Invalid username or password").
- `realm`: Branding details, registration toggles, and password policy indicators.
- `auth`: Multi-factor authentication states and credential requirements.

Here is a clean custom login component built with Keycloakify:

src/login/pages/Login.tsx:
```tsx
import { useState } from 'react';
import type { PageProps } from 'keycloakify/login/pages/PageProps';
import type { KcContext } from '../KcContext';
import type { I18n } from '../i18n';

export default function Login(props: PageProps<Extract<KcContext, { pageId: 'login.ftl' }>, I18n>) {
    const { kcContext, i18n } = props;
    const { url, realm, messagesPerField } = kcContext;

    const [isSubmitting, setIsSubmitting] = useState(false);

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div className="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-sm border border-gray-200">
                <div>
                    <h2 className="text-center text-3xl font-extrabold text-gray-900">
                        Sign in to {realm.displayName || 'Account'}
                    </h2>
                </div>

                <form
                    action={url.loginAction}
                    method="post"
                    onSubmit={() => setIsSubmitting(true)}
                    className="mt-8 space-y-6"
                >
                    <div className="rounded-md shadow-sm space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Username or Email</label>
                            <input
                                name="username"
                                type="text"
                                required
                                defaultValue={kcContext.login?.username ?? ''}
                                className="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300"
                            />
                            {messagesPerField.existsError('username') && (
                                <p className="text-red-600 text-xs mt-1">
                                    {messagesPerField.getFirstError('username')}
                                </p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700">Password</label>
                            <input
                                name="password"
                                type="password"
                                required
                                className="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300"
                            />
                            {messagesPerField.existsError('password') && (
                                <p className="text-red-600 text-xs mt-1">
                                    {messagesPerField.getFirstError('password')}
                                </p>
                            )}
                        </div>
                    </div>

                    <button
                        type="submit"
                        disabled={isSubmitting}
                        className="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                    >
                        {isSubmitting ? 'Signing in...' : 'Sign In'}
                    </button>
                </form>
            </div>
        </div>
    );
}
```

Notice the form attributes: `action={url.loginAction}` and `method="post"`. The form performs a real browser POST directly to Keycloak's internal endpoint. Keycloak processes the session, updates its authentication state machine, and issues the appropriate OAuth redirects.

## Develop Quickly Using Storybook

One of the worst aspects of traditional Keycloak theme development is waiting for a local Docker container running Java to recompile templates on every CSS change.

Keycloakify solves this by providing mock contexts inside Storybook. You can mock any authentication state without running Keycloak:

src/login/pages/Login.stories.tsx:
```tsx
import type { Meta, StoryObj } from '@storybook/react';
import { createKcPageStory } from 'keycloakify/login';

const { KcPageStory } = createKcPageStory({ pageId: 'login.ftl' });

const meta: Meta<typeof KcPageStory> = {
    title: 'login/Login',
    component: KcPageStory,
};

export default meta;

type Story = StoryObj<typeof KcPageStory>;

export const Default: Story = {};

export const WithErrorMessage: Story = {
    args: {
        kcContext: {
            messagesPerField: {
                existsError: (field: string) => field === 'password',
                getFirstError: () => 'Invalid credentials specified.',
            },
        },
    },
};
```

Run `npm run storybook`, and you can visually verify your login screen, error boundaries, language pickers, and mobile responsiveness in milliseconds.

## Building and Deploying the Theme JAR

When your design is ready, Keycloakify's build script bundles your React application, generates the underlying FreeMarker templates, and packages them into a production-ready `.jar` file:

```bash
npm run build-keycloak-theme
```

This creates an archive in `dist_keycloak/keycloak-theme-for-kc-all-other-versions.jar`.

In your Dockerfile or deployment pipeline, copy this JAR into Keycloak's `providers/` directory:

Dockerfile:
```dockerfile
FROM quay.io/keycloak/keycloak:24.0

COPY dist_keycloak/*.jar /opt/keycloak/providers/

RUN /opt/keycloak/bin/kc.sh build

ENTRYPOINT ["/opt/keycloak/bin/kc.sh", "start", "--optimized"]
```

When Keycloak boots, navigate to the Realm Settings in the Admin Console, open the **Themes** tab, and select your custom theme from the **Login Theme** dropdown.

## What Can Go Wrong

The most catastrophic error frontend engineers make with Keycloakify is intercepting the form submission with `event.preventDefault()` to submit data via Axios or Fetch:

```tsx
// Catastrophic error: do NOT submit via Fetch or Axios
const handleSubmit = async (e) => {
    e.preventDefault();
    await fetch(url.loginAction, { method: 'POST', body: JSON.stringify(data) });
};
```

Keycloak's authentication SPI depends fundamentally on HTTP 302 redirects, session cookies, and browser navigation state. If you submit via Fetch, the browser does not follow the session redirect, cookies fail to bind to the client origin, and the user remains stuck on the login screen. Always allow the browser to perform a native form POST.

## Summary

Customizing Keycloak does not require struggling with raw FreeMarker templates or hacking together an insecure custom auth backend. Keycloakify lets you use the full power of modern React, Tailwind, and Storybook while strictly respecting Keycloak's authentication lifecycle.

Preserve the native form POST, consume `kcContext` for server errors and actions, and test your edge cases in Storybook. You will deliver a world-class login experience while keeping your authentication rock-solid.

## Further Reading

- [Keycloakify Official Documentation](https://www.keycloakify.dev/)
- [Keycloak Server Administration Guide: Themes](https://www.keycloak.org/docs/latest/server_development/#_themes)
- [Storybook Component Driven Development](https://storybook.js.org/)
- [Keycloak OpenID Connect Implementation](https://www.keycloak.org/docs/latest/securing_apps/#_oidc)

Have you customized Keycloak authentication screens with React? Tell us about your deployment setup in the comments below.
