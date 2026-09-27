# Laravel with Inertia and Vue: Composables Are Glue, Not a Second Backend

You open an Inertia Vue project and find a Pinia store caching customer records, a composable calculating tax rates, and an Axios client running alongside Inertia's router. The frontend team brought Nuxt and SPA habits to an architecture where Laravel already manages state, validation, and authentication.

Inertia with Vue 3 works best when you keep the client layer thin. Laravel provides the data through page props, Vue components render the template, and composables handle small, reusable UI behaviors. If you avoid building a shadow backend inside your Vue application, you eliminate stale state bugs and cut your frontend bundle in half.

## Let Laravel Own the Data Model

When you use Inertia, the server remains the authoritative source of truth. Your Laravel controller handles database queries, applies business rules, and passes plain data objects directly into Vue components.

Here is a typical controller returning a paginated list:

app/Http/Controllers/CustomerController.php:
```php
namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Customers/Index', [
            'customers' => Customer::query()
                ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }
}
```

The component accepts these props via Vue 3's `<script setup>` syntax and renders them without an intermediate state store:

resources/js/Pages/Customers/Index.vue:
```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface Customer {
    id: number;
    name: string;
    email: string;
    company: string;
}

defineProps<{
    customers: {
        data: Customer[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { search?: string };
}>();
</script>

<template>
    <div class="p-6 max-w-5xl mx-auto">
        <Head title="Customers" />
        <h1 class="text-xl font-bold mb-4">Customer Directory</h1>

        <div class="border rounded divide-y divide-gray-100">
            <div v-for="customer in customers.data" :key="customer.id" class="p-3 flex justify-between">
                <div>
                    <div class="font-medium">{{ customer.name }}</div>
                    <div class="text-sm text-gray-500">{{ customer.email }}</div>
                </div>
                <div class="text-sm text-gray-400">{{ customer.company }}</div>
            </div>
        </div>
    </div>
</template>
```

You do not need a store to hold `customers`. When page navigation happens or pagination links are clicked, Inertia updates the props automatically and Vue's reactivity system handles the DOM update.

## The Anti-Pattern: Building a Shadow Domain in Pinia

The most frequent mistake in Inertia Vue apps is installing Pinia or Vuex to replicate backend state. Developers write stores that fetch from `/api/customers`, hold arrays of models, and implement client-side tax calculations:

resources/js/stores/useCustomerStore.ts:
```ts
// Anti-pattern: do not duplicate backend state in a client store
import { defineStore } from 'pinia';
import axios from 'axios';

export const useCustomerStore = defineStore('customer', {
    state: () => ({
        list: [],
        currentOrder: null,
    }),
    actions: {
        async calculateTax(amount: number) {
            // Replicating backend logic on the client
            return amount * 0.15;
        },
        async fetchCustomers() {
            const res = await axios.get('/api/customers');
            this.list = res.data;
        }
    }
});
```

This pattern creates synchronization nightmares. When a record updates on the server, the Pinia store contains stale data until manually refreshed. Tax calculations get out of sync with Laravel's checkout calculations.

Keep business logic, tax rules, and authorization on the server. If the client needs calculated values, calculate them in Laravel and pass them down as props.

## What Composables Are Actually For

Composables in an Inertia app should provide reusable UI behavior—such as debouncing user input, managing modal open states, or observing viewport size—never domain operations.

Here is a clean composable for debouncing live search inputs across different list pages:

resources/js/Composables/useDebouncedFilter.ts:
```ts
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

export function useDebouncedFilter(initialValue: string = '', routeName: string, queryParam: string = 'search') {
    const filter = ref(initialValue);

    const updateQuery = debounce((value: string) => {
        router.get(
            route(routeName),
            { [queryParam]: value || undefined },
            { preserveState: true, replace: true }
        );
    }, 300);

    watch(filter, (newValue) => {
        updateQuery(newValue);
    });

    return { filter };
}
```

This composable has one clear responsibility: it delays the Inertia visit until the user pauses typing, preserves the component scroll state, and replaces the browser history entry. Any list page can use it in two lines of code without knowing how the database filters.

## Mutations with Inertia's useForm

For creating and editing records, use Inertia's `useForm` composable. It manages dirty state, tracks processing status, and exposes server validation errors directly to your template.

resources/js/Pages/Customers/Create.vue:
```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    company: '',
});

function submit() {
    form.post('/customers', {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <form @submit.prevent="submit" class="max-w-md space-y-4">
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input v-model="form.name" type="text" class="w-full border rounded p-2" />
            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium">Email</label>
            <input v-model="form.email" type="email" class="w-full border rounded p-2" />
            <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
        </div>

        <button :disabled="form.processing" type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">
            Create Customer
        </button>
    </form>
</template>
```

When validation fails in your Laravel Form Request, `form.errors` immediately reflects the error messages returned from the server. You write zero client-side validation logic and zero custom HTTP error handling.

## What Can Go Wrong

A common mistake when using Vue with Inertia is mutating props directly inside a child component. Vue's reactivity system tracks prop changes from the server, but attempting to modify a prop locally triggers a warning and can cause desynchronized states.

If you need a local copy of a prop for editing, initialize a local `ref` or pass it into `useForm`:

```ts
// Incorrect: mutating prop directly
props.customer.name = 'New Name';

// Correct: initialize form state with the prop
const form = useForm({
    name: props.customer.name,
});
```

When the form submission completes and Laravel redirects, Inertia sends fresh props from the server, cleanly updating the view.

## Summary

Inertia eliminates the need for frontend state stores in Vue applications. Treat page props as the single source of truth, rely on `useForm` for stateful submissions, and keep your composables focused on UI utilities.

By keeping your Vue components lightweight and letting Laravel handle business rules, you reduce code duplication, avoid caching bugs, and build an application that is a pleasure to maintain.

## Further Reading

- [Inertia.js Vue 3 Setup](https://inertiajs.com/client-side-setup#vue-3)
- [Inertia.js Form Submissions](https://inertiajs.com/forms)
- [Vue 3 Composition API Documentation](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Laravel Inertia Shared Data](https://inertiajs.com/shared-data)

What patterns have worked best for your team when building Vue 3 frontends with Inertia? Join the conversation in the comments.
