---
title: "How to Prevent Double-Submissions with a Custom Vue 3 Click Guard Composable"
published: true
description: "Eliminate duplicate database records and concurrent API submissions using a lightweight, reusable useClickGuard composable in Vue 3."
tags: "vue, composables, forms, api, concurrency"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/83-prevent-double-click-submissions-vue.md"
---

A common source of database bugs in web applications is the double-click submission. When a user clicks "Submit Payment", "Issue Policy", or "Create Order" multiple times while waiting on a slow network connection, the application dispatches multiple concurrent requests.

While backend idempotency keys protect against duplicate processing, defensive frontend design should prevent redundant requests from leaving the browser. A reusable `useClickGuard` composable locks interaction states cleanly across Vue 3 components. Let's see how.

## The Problem: The Slow Network Click Storm

Consider a standard Vue button handler without concurrency protection:

```vue
<button @click="submitQuotation">Issue Policy</button>
```

When network latency spikes or a downstream API takes 1,500ms to respond, impatient users instinctively click the button repeatedly. Without button debouncing or interaction guards, the browser dispatches three HTTP POST requests in rapid succession:

```text
POST /api/policies/issue ──> [Pending]
POST /api/policies/issue ──> [Pending]  <-- Duplicate charge attempt
POST /api/policies/issue ──> [Pending]  <-- Third duplicate attempt
```

If the backend lacks strict database transaction locks, duplicate records or double charges are created.

## Step 1: Implement the useClickGuard Composable

Build a composable that manages an active execution lock and includes a brief cool-down timeout to block rapid accidental clicks:

`resources/js/utils/useClickGuard.js:`
```javascript
import { ref } from 'vue';

/**
 * Prevents execution of an async or sync handler while a previous call is in progress.
 * Includes a cool-down window to prevent immediate re-clicks after completion.
 *
 * @param {number} cooldownMs Post-resolution cooldown in milliseconds (default: 350ms)
 * @returns {{ busy: import('vue').Ref<boolean>, guard: (fn: Function) => Function }}
 */
export function useClickGuard(cooldownMs = 350) {
    const busy = ref(false);

    const guard = (actionFn) => {
        return async (...args) => {
            // Drop execution if an operation is currently running or cooling down
            if (busy.value) {
                return;
            }

            busy.value = true;
            try {
                await actionFn(...args);
            } finally {
                // Keep the button disabled during the cool-down window
                setTimeout(() => {
                    busy.value = false;
                }, cooldownMs);
            }
        };
    };

    return {
        busy,
        guard,
    };
}
```

The composable wraps any async function in a `try/finally` block. Regardless of whether the request succeeds or throws an error, the lock releases safely once the cooldown period expires.

## Step 2: Bind the Composable to Form Actions

Connect the click guard to your component's button and action handlers:

`resources/js/views/Policy/IssueModal.vue:`
```vue
<script setup>
import { useClickGuard } from '@/utils/useClickGuard';
import { notify } from '@/notify';
import { policyService } from '@/services/policy.service';

const props = defineProps({
    quotationId: {
        type: [String, Number],
        required: true,
    },
});

const emit = defineEmits(['issued', 'close']);

const { busy, guard } = useClickGuard(500);

const handleIssuePolicy = guard(async () => {
    try {
        const policy = await policyService.issueFromQuotation(props.quotationId);
        notify('Policy issued successfully.', 'success');
        emit('issued', policy);
    } catch (error) {
        notify(error.message || 'Failed to issue policy.', 'error');
    }
});
</script>

<template>
    <div class="p-6 bg-white rounded-lg shadow-md max-w-sm mx-auto">
        <h3 class="text-base font-bold text-slate-800 mb-2">Confirm Policy Issuance</h3>
        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
            Issuing this policy commits the premium schedule and generates the official certificate.
        </p>

        <div class="flex justify-end gap-3">
            <button
                type="button"
                :disabled="busy"
                @click="emit('close')"
                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 disabled:opacity-50"
            >
                Cancel
            </button>

            <button
                type="button"
                :disabled="busy"
                @click="handleIssuePolicy"
                class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 rounded transition flex items-center gap-2"
            >
                <span v-if="busy" class="inline-block animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                <span>{{ busy ? 'Processing...' : 'Confirm Issuance' }}</span>
            </button>
        </div>
    </div>
</template>
```

When users click the button, `busy` becomes `true` synchronously before the async API call begins. The button is disabled immediately, preventing subsequent clicks during the operation.

## What Can Go Wrong

- **Omitting `finally` Cleanup**: If an API call fails and the action handler does not use a `try/finally` structure, `busy` can remain `true` indefinitely, leaving the button permanently locked. The composable's internal `try/finally` block guards against this.
- **Ignoring Backend Idempotency**: Frontend click guards block accidental duplicate clicks from standard users, but they do not protect against network replay attacks or scripts. Critical financial operations must always be backed by backend idempotency keys.

## Summary

Frontend double-submission bugs create duplicate records and degrade the user experience. By wrapping async action handlers in a reusable `useClickGuard` composable, Vue 3 applications prevent duplicate requests at the UI layer while providing clear loading feedback to users.

## Further Reading

- [Vue 3 Composition API Overview](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Stripe Documentation: Idempotent Requests](https://stripe.com/docs/api/idempotent_requests)
- [MDN Button Disabled Attribute](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button#disabled)

Add a `useClickGuard` composable to your Vue toolkit to protect financial submissions and form workflows against double-click duplicates.
