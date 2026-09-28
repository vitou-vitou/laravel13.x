---
title: "How to Build a Type-Safe Direct Book Insurance Journey in Vue 3"
published: true
description: "Structure a unified multi-product insurance workflow across Quote, Policy, and Endorsement stages in a Vue 3 administrative application."
tags: "vue, typescript, architecture, forms, bff"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/84-direct-book-insurance-journey-vue.md"
---

Enterprise insurance platforms often support diverse product lines—such as Burglary, Marine Cargo, Directors & Officers, and Contractor's All Risk (CAR)—each with unique underwriting schedules, interest tables, and deductible rules. Teams frequently build separate, disconnected interfaces for each product line, leading to duplicated code, inconsistent form behavior, and high maintenance overhead.

The Direct Book journey pattern unifies these varied insurance lines into a shared lifecycle pipeline: Quotation creation, Policy issuance, and Endorsement adjustments. Let's see how.

## The Architecture: Catalog-Driven Product Routing

Rather than hardcoding custom views for every insurance line, the Direct Book pattern uses a centralized product catalog. This catalog maps product codes (e.g., `0198` for D&O, `0201` for Marine Cargo) to their corresponding underwriting family:

```text
[Product Code] ──> [Catalog Driver] ──> Underwriting Family (Property | Engineering | Liability | Transit)
                                       └──> Shared Shell: Quote ──> Policy ──> Endorsement
```

This architecture allows the application to share common shell elements—such as customer selectors, policy terms, and premium summaries—while delegating product-specific interest tables to dedicated sub-components.

## Step 1: Define the Central Catalog Configuration

Store the official Direct Book line definitions in a centralized data catalog:

`resources/data/direct-book-lines.json:`
```json
[
  {
    "code": "0121",
    "name": "Burglary Insurance",
    "family": "Property",
    "hasSchedule": true,
    "hasDeductibles": true
  },
  {
    "code": "0198",
    "name": "Directors & Officers Liability",
    "family": "Liability",
    "hasSchedule": false,
    "hasDeductibles": true
  },
  {
    "code": "0201",
    "name": "Marine Cargo",
    "family": "Transit",
    "hasSchedule": true,
    "hasDeductibles": false
  }
]
```

Create a lightweight helper to query product metadata without tight coupling to specific views:

`resources/js/helpers/directBookCatalog.js:`
```javascript
import directBookLines from '@/../data/direct-book-lines.json';

const catalogMap = new Map(directBookLines.map((line) => [line.code, line]));

export function getProductConfig(code) {
    return catalogMap.get(String(code)) || null;
}

export function isDirectBookProduct(code) {
    return catalogMap.has(String(code));
}
```

This helper serves as the single source of truth across navigation menus, quote forms, and policy view components.

## Step 2: Implement the Shared Journey Shell

Build a master layout component that manages step progress and coordinates common form data:

`resources/js/views/PropertyLiability/DirectBookShell.vue:`
```vue
<script setup>
import { computed } from 'vue';
import { getProductConfig } from '@/helpers/directBookCatalog';

const props = defineProps({
    productCode: {
        type: String,
        required: true,
    },
    currentStage: {
        type: String, // 'quote' | 'policy' | 'endorsement'
        default: 'quote',
    },
});

const product = computed(() => getProductConfig(props.productCode));
</script>

<template>
    <div class="max-w-6xl mx-auto py-6 px-4">
        <!-- Breadcrumb Header -->
        <div class="flex items-center justify-between border-b pb-4 mb-6">
            <div>
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">
                    {{ product?.family || 'Commercial Lines' }}
                </span>
                <h1 class="text-2xl font-bold text-slate-800">
                    {{ product?.name }} &bull; {{ currentStage.toUpperCase() }}
                </h1>
            </div>

            <!-- Lifecycle Stage Tracker -->
            <div class="flex items-center gap-2">
                <span :class="['px-3 py-1 rounded-full text-xs font-semibold', currentStage === 'quote' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500']">
                    1. Quote
                </span>
                <span class="text-slate-300">&rarr;</span>
                <span :class="['px-3 py-1 rounded-full text-xs font-semibold', currentStage === 'policy' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500']">
                    2. Policy
                </span>
                <span class="text-slate-300">&rarr;</span>
                <span :class="['px-3 py-1 rounded-full text-xs font-semibold', currentStage === 'endorsement' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500']">
                    3. Endorsement
                </span>
            </div>
        </div>

        <!-- Render Product-Specific Schedule or Details Slot -->
        <slot :product="product"></slot>
    </div>
</template>
```

The shell provides consistent navigation and lifecycle tracking while allowing individual product lines to render custom fields inside the default slot.

## Step 3: Mount Product-Specific Schedule Panels

Incorporate product-specific interest tables—such as Marine transit routes or Burglary premises items—into the shared shell:

`resources/js/views/PropertyLiability/Burglary/BurglaryQuote.vue:`
```vue
<script setup>
import DirectBookShell from '../DirectBookShell.vue';
import { pairGrid, pairCell, pairFull } from '@/services/property_liability/burglary/pair-grid';
</script>

<template>
    <DirectBookShell productCode="0121" currentStage="quote">
        <template #default="{ product }">
            <div class="bg-white p-6 rounded-lg border shadow-sm space-y-6">
                <h2 class="text-lg font-semibold text-slate-800">Underwriting Information</h2>

                <div :class="pairGrid">
                    <div :class="pairCell">
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Customer / Insured</label>
                        <input type="text" class="w-full border rounded px-3 py-2 text-sm" placeholder="Search account..." />
                    </div>

                    <div :class="pairCell">
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Occupation / Industry</label>
                        <input type="text" class="w-full border rounded px-3 py-2 text-sm" />
                    </div>

                    <div :class="pairFull">
                        <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Premises Address</label>
                        <textarea rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
            </div>
        </template>
    </DirectBookShell>
</template>
```

By standardizing on a shared shell, updates to the customer selector or premium calculation engine automatically apply across all product lines.

## What Can Go Wrong

- **Leaking Product-Specific Logic into the Shared Shell**: Adding conditional checks like `v-if="productCode === '0198'"` inside the shell introduces tight coupling. Keep product-specific fields encapsulated within their respective sub-components.
- **Unverified Catalog Codes**: If a user navigates to an unlisted product code, views can throw errors when reading undefined properties. Always use defensive lookups (`getProductConfig(code) || null`) and render clear error states for invalid codes.

## Summary

The Direct Book journey pattern structures varied insurance lines into a shared, predictable lifecycle. By centralizing product catalog definitions and wrapping views in a common shell, development teams can launch new insurance products quickly while maintaining consistent workflows across quotes, policies, and endorsements.

## Further Reading

- [Vue 3 Slots Architecture](https://vuejs.org/guide/components/slots.html)
- [Enterprise Component Composition Patterns](https://martinfowler.com/articles/micro-frontends.html)
- [Managing Dynamic Form Schemas](https://vuejs.org/guide/scaling-up/state-management.html)

Standardize your insurance workflows using a shared journey shell to simplify product onboarding and lifecycle management.
