---
title: "How to Build Declarative Permission Directives in Vue 3 Admin Panels"
published: true
description: "Implement custom Vue 3 v-can directives, RBAC helpers, and route guards that stay in sync with server-side authorization policies."
tags: "vue, permissions, security, rbac, directives"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/86-declarative-vue-permission-directives.md"
---

Enterprise administrative dashboards require fine-grained access control. Different roles—such as underwriters, claims officers, finance clerks, and system administrators—need access to different buttons, form fields, and navigation links.

Scattering raw permission checks like `if (user.roles.includes('admin') || user.permissions.includes('policy.issue'))` throughout templates produces cluttered, hard-to-maintain code. Implementing declarative `v-can` directives and unified permission helpers keeps authorization clean and consistent across the frontend. Let's see how.

## The Architecture: Synchronizing Server Policies with the Frontend

Frontend permission checks improve the user experience by hiding inaccessible controls, but they do not replace backend security. The server remains the ultimate authority for authorization:

```text
Laravel Policies (Gatekeeper) ──[Serialized User Permissions]──> Vue Pinia/State Store ──> v-can Directive
```

When a user logs in, the backend serializes their effective abilities into the session. The frontend stores these capabilities and evaluates them via custom Vue directives and route guards.

## Step 1: Manage Permissions in a Reactive Store

Centralize user permission state inside a lightweight reactive store:

`resources/js/store/auth.js:`
```javascript
import { reactive, computed } from 'vue';

const state = reactive({
    user: null,
    permissions: new Set(),
});

export const useAuthStore = () => {
    const setUser = (userData) => {
        state.user = userData;
        state.permissions = new Set(userData?.permissions || []);
    };

    const hasPermission = (permission) => {
        if (!permission) return true;
        // Global Super Admin bypass
        if (state.permissions.has('*') || state.permissions.has('system.admin')) {
            return true;
        }
        return state.permissions.has(permission);
    };

    const hasAnyPermission = (permissionList = []) => {
        return permissionList.some((perm) => hasPermission(perm));
    };

    return {
        user: computed(() => state.user),
        setUser,
        hasPermission,
        hasAnyPermission,
    };
};
```

Using a JavaScript `Set` provides constant-time \(O(1)\) lookup performance when evaluating permissions across large component trees.

## Step 2: Implement the Custom v-can Directive

Create a custom Vue directive that removes elements from the DOM if the current user lacks the required permission:

`resources/js/directives/can.js:`
```javascript
import { useAuthStore } from '@/store/auth';

export const canDirective = {
    mounted(el, binding) {
        const { hasPermission } = useAuthStore();
        const requiredPermission = binding.value;

        if (!hasPermission(requiredPermission)) {
            // Remove unauthorized element from the DOM
            el.parentNode?.removeChild(el);
        }
    },
    updated(el, binding) {
        const { hasPermission } = useAuthStore();
        const requiredPermission = binding.value;

        if (!hasPermission(requiredPermission)) {
            el.parentNode?.removeChild(el);
        }
    },
};
```

Register the directive globally during application bootstrap:

`resources/js/app.js:`
```javascript
import { createApp } from 'vue';
import App from './Container.vue';
import { canDirective } from './directives/can';

const app = createApp(App);

app.directive('can', canDirective);
app.mount('#app');
```

The directive removes unauthorized elements from the DOM entirely rather than simply hiding them via CSS (`display: none`), preventing sensitive actions from being inspected or modified through browser developer tools.

## Step 3: Apply the Directive in Components and Route Guards

Use the `v-can` directive directly in component templates:

`resources/js/views/Quotation/Show.vue:`
```vue
<script setup>
import { useAuthStore } from '@/store/auth';

const props = defineProps({
    quotation: {
        type: Object,
        required: true,
    },
});

const { hasPermission } = useAuthStore();
</script>

<template>
    <div class="flex justify-between items-center p-6 bg-white border-b">
        <h1 class="text-xl font-bold text-slate-800">Quotation #{{ quotation.reference }}</h1>

        <div class="flex gap-2">
            <!-- Edit draft is visible to standard underwriters -->
            <button
                v-can="'quotation.edit'"
                class="px-3 py-1.5 border rounded text-xs font-semibold text-slate-700 hover:bg-slate-50"
            >
                Edit Draft
            </button>

            <!-- Issue Policy is restricted to senior staff -->
            <button
                v-can="'policy.issue'"
                class="px-3 py-1.5 bg-blue-600 rounded text-xs font-semibold text-white hover:bg-blue-700"
            >
                Issue Official Policy
            </button>

            <!-- Inline script check for complex conditional logic -->
            <button
                v-if="hasPermission('quotation.override') && quotation.requiresSpecialApproval"
                class="px-3 py-1.5 bg-amber-500 rounded text-xs font-semibold text-white hover:bg-amber-600"
            >
                Apply Underwriter Override
            </button>
        </div>
    </div>
</template>
```

Add matching route guards to prevent unauthorized users from navigating to restricted URLs directly:

`resources/js/router/router.js:`
```javascript
import { useAuthStore } from '@/store/auth';

router.beforeEach((to, from, next) => {
    const requiredPermission = to.meta?.permission;
    const { hasPermission } = useAuthStore();

    if (requiredPermission && !hasPermission(requiredPermission)) {
        // Redirect unauthorized users to a dedicated error view
        return next({ name: 'unauthorized' });
    }

    next();
});
```

Route guards intercept navigation attempts before components mount, keeping unauthorized views protected.

## What Can Go Wrong

- **Relying on Frontend-Only Authorization**: Removing a button with `v-can` prevents clicks in the UI, but it does not stop a user from issuing the same HTTP request via `curl` or Postman. Backend routes must always enforce authorization independently using Laravel Policies or middleware.
- **Stale Permission Sets**: If a user's permissions change on the server while their session is active, their frontend store may hold outdated capabilities until the page reloads. Re-fetch permissions whenever a `403 Forbidden` response is received.

## Summary

Declarative `v-can` directives and centralized permission stores simplify access control across complex Vue applications. By removing unauthorized DOM elements and guarding routes proactively, teams deliver a clean, role-tailored user experience backed by robust server-side security.

## Further Reading

- [Vue 3 Custom Directives Guide](https://vuejs.org/guide/reusability/custom-directives.html)
- [Laravel Authorization Policies](https://laravel.com/docs/authorization#creating-policies)
- [NIST Role-Based Access Control (RBAC) Standard](https://csrc.nist.gov/projects/role-based-access-control)

Implement a `v-can` directive in your Vue admin panel to streamline role-based UI access control.
