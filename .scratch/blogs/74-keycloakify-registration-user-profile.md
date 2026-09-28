---
title: "How to Build Custom Registration and User Profile Forms in Keycloakify"
published: true
description: "Handle custom user profile attributes, dynamic field validation, and terms of service checkboxes inside Keycloakify themes."
tags: "keycloak, keycloakify, user-profile, registration, forms"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/74-keycloakify-registration-user-profile.md"
---

When enterprise applications require user registration, the standard registration fields (username, email, password) are rarely sufficient. Organizations often need to collect custom metadata—such as departmental codes, company names, or phone numbers—while enforcing terms of service compliance.

Keycloak's User Profile SPI manages these custom attributes server-side, and Keycloakify exposes them via `kcContext` for type-safe frontend rendering. Let's see how.

## The Architecture: Keycloak Declarative User Profile

In Keycloak 24+, the Declarative User Profile feature is enabled by default. Administrators define custom attributes (e.g., `department`, `phoneNumber`, `acceptTerms`) in the realm console. When rendering `register.ftl`, Keycloak passes metadata for these attributes—including labels, validation patterns, and required flags—inside `kcContext.profile`.

Using this profile metadata in Keycloakify ensures registration forms automatically adapt to backend attribute changes without manual updates to frontend templates.

## Step 1: Inspect the Profile Context Model

Keycloakify structures profile fields in `register.ftl` with type-safe properties:

`src/login/pages/Register.tsx:`
```tsx
import type { KcContext } from '../KcContext';

type Props = {
    kcContext: Extract<KcContext, { pageId: 'register.ftl' }>;
};

export default function Register({ kcContext }: Props) {
    const { url, profile, messagesPerField, realm } = kcContext;
    const { attributes } = profile;

    return (
        <div className="max-w-lg mx-auto my-10 bg-white p-8 rounded-xl shadow-md border border-slate-200">
            <h1 className="text-2xl font-bold text-slate-800 mb-2">Create Account</h1>
            <p className="text-sm text-slate-500 mb-6">
                Register for your {realm.displayName || 'Corporate'} credentials
            </p>

            <form action={url.registrationAction} method="post" className="space-y-4">
                {/* Dynamically render fields defined in Realm User Profile */}
                {attributes.map((attribute) => {
                    const { name, displayName, required, value, readOnly, annotations } = attribute;
                    const hasError = messagesPerField.existsError(name);
                    const errorMessage = messagesPerField.getFirstError(name);

                    return (
                        <div key={name}>
                            <label className="block text-sm font-medium text-slate-700 mb-1">
                                {displayName || name} {required && <span className="text-red-500">*</span>}
                            </label>

                            <input
                                type={annotations?.inputType === 'password' ? 'password' : 'text'}
                                name={name}
                                defaultValue={value ?? ''}
                                readOnly={readOnly}
                                required={required}
                                className={`w-full px-3 py-2 border rounded-md text-sm transition focus:outline-none focus:ring-2 ${
                                    hasError
                                        ? 'border-red-400 focus:ring-red-400'
                                        : 'border-slate-300 focus:ring-blue-500'
                                }`}
                            />

                            {hasError && (
                                <p className="text-xs text-red-600 mt-1">{errorMessage}</p>
                            )}
                        </div>
                    );
                })}

                {/* Terms of Service Acceptance */}
                {realm.registrationEmailAsUsername && (
                    <div className="flex items-start gap-2 pt-2">
                        <input
                            type="checkbox"
                            name="termsAccepted"
                            id="termsAccepted"
                            required
                            className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 mt-0.5"
                        />
                        <label htmlFor="termsAccepted" className="text-xs text-slate-600 leading-snug">
                            I agree to the <a href={url.loginAction} className="text-blue-600 underline">Terms of Service</a> and Privacy Policy.
                        </label>
                    </div>
                )}

                <div className="pt-4">
                    <button
                        type="submit"
                        className="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition"
                    >
                        Complete Registration
                    </button>
                </div>
            </form>
        </div>
    );
}
```

This dynamic approach renders standard fields (username, email) and custom attributes (department, employee ID) through a unified, accessible form template.

## Step 2: Handle Explicit Custom Fields

For registration forms that require a specific layout—such as placing First Name and Last Name side by side—you can target known attributes explicitly:

`src/login/pages/RegisterCustomLayout.tsx:`
```tsx
import type { KcContext } from '../KcContext';

export default function RegisterCustomLayout({
    kcContext,
}: {
    kcContext: Extract<KcContext, { pageId: 'register.ftl' }>;
}) {
    const { url, messagesPerField } = kcContext;

    return (
        <form action={url.registrationAction} method="post" className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
                <div>
                    <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">First Name</label>
                    <input
                        type="text"
                        name="firstName"
                        defaultValue={kcContext.register?.formData?.firstName ?? ''}
                        required
                        className="w-full border rounded px-3 py-2 text-sm"
                    />
                </div>
                <div>
                    <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Last Name</label>
                    <input
                        type="text"
                        name="lastName"
                        defaultValue={kcContext.register?.formData?.lastName ?? ''}
                        required
                        className="w-full border rounded px-3 py-2 text-sm"
                    />
                </div>
            </div>

            <div>
                <label className="block text-xs font-semibold text-slate-700 uppercase mb-1">Corporate Department</label>
                <input
                    type="text"
                    name="user.attributes.department"
                    defaultValue={kcContext.register?.formData?.['user.attributes.department'] ?? ''}
                    placeholder="Engineering / Sales"
                    className="w-full border rounded px-3 py-2 text-sm"
                />
            </div>

            <button type="submit" className="w-full bg-slate-900 text-white py-2 rounded text-sm font-medium">
                Submit Registration
            </button>
        </form>
    );
}
```

Using `name="user.attributes.<attributeName>"` maps the input value directly to Keycloak's user attribute storage.

## What Can Go Wrong

- **Missing User Profile SPI Permissions**: If custom attributes are marked as read-only or hidden in the Keycloak admin console, Keycloak will silently drop them on form submission. Ensure permissions are set to "User can edit".
- **Nested Attribute Formatting**: Submitting custom attributes as `name="department"` rather than `name="user.attributes.department"` causes Keycloak to discard the field on older server configurations. Verify the expected input name format in your Keycloak version.

## Summary

Combining Keycloak's Declarative User Profile with Keycloakify provides a flexible registration workflow. Whether rendering dynamic attributes from realm configuration or building a tailored multi-column layout, user metadata is collected reliably and bound securely to Keycloak's authentication pipeline.

## Further Reading

- [Keycloak Declarative User Profile Documentation](https://www.keycloak.org/docs/latest/server_admin/#user-profile)
- [Keycloakify Form Attributes Guide](https://docs.keycloakify.dev/form-attributes)
- [Accessible Forms Guidance](https://www.w3.org/WAI/tutorials/forms/labels/)

Configure user profile fields in your Keycloak realm to build flexible, validated registration views in Keycloakify.
