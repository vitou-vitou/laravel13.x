---
title: "How to Fix Vite and Production CSS Order Traps in Tailwind Projects"
published: true
description: "Debug and prevent CSS cascade ordering bugs where unscoped legacy styles override Tailwind utility classes after Docker production builds."
tags: "tailwind, vite, css, frontend, devops"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/81-vite-production-css-cascade-traps.md"
---

A frustrating frontend bug occurs when a layout looks pixel-perfect during local development under `npm run dev`, but completely breaks when deployed to production via Docker or a staging server. Multi-column grids collapse into single columns, margins disappear, and utility classes seem to be ignored.

This disparity stems from CSS bundle concatenation order. During local development, Vite injects stylesheets dynamically into the DOM via Hot Module Replacement (HMR). In production, `@vite` bundles and appends stylesheets in the order they are linked. Let's see how.

## The Problem: The Unscoped Legacy CSS Trap

Many enterprise applications migrate incrementally from legacy stylesheets (such as `core.css`) to utility frameworks like Tailwind CSS. Consider an application loading both stylesheets in its root layout:

`resources/views/app.blade.php:`
```blade
<head>
    <!-- Vite asset directive -->
    @vite(['resources/css/app.css', 'resources/css/core.css'])
</head>
```

Suppose `core.css` contains legacy global rules:

`resources/css/core.css:`
```css
/* Legacy global grid rules lacking media queries */
.grid-cols-1 {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.grid-cols-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}
```

Now, consider a Vue component utilizing Tailwind responsive classes:

`resources/js/views/Quotation/Create.vue:`
```vue
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>Customer Information</div>
    <div>Policy Period</div>
</div>
```

During local `npm run dev`, Vite injects Tailwind styles last via HMR, making `md:grid-cols-2` win the cascade. But when running `npm run build` for production, `core.css` is concatenated after `app.css`. Because `core.css` has equal selector specificity (`.grid-cols-1`) and appears later in the document head, the single-column layout wins permanently on all desktop screens.

## Step 1: Detect the Bug Locally Without Deploying

Never wait for a production Docker container build to discover CSS ordering regressions. You can reproduce production asset behavior locally in two steps:

`Terminal:`
```bash
# Stop your Vite dev server, compile production assets, and serve via Artisan
npm run build
php artisan serve
```

Inspecting the computed styles in DevTools will immediately reveal whether `.grid-cols-1` from `core.css` is overriding your responsive classes.

## Step 2: Implement the Safe 4-Column Grid Pattern

Rather than refactoring thousands of lines in legacy vendor stylesheets, adopt a defensive grid structure that bypasses `.grid-cols-1` / `.grid-cols-2` collisions entirely:

`resources/js/services/property_liability/burglary/pair-grid.js:`
```javascript
// Centralized CSS class definitions immune to core.css cascade order
export const pairGrid = 'grid grid-cols-4 gap-4';
export const pairCell = 'col-span-2';
export const pairFull = 'col-span-4';
export const pairSpacer = 'col-span-2 hidden md:block';

export const pairSlot = (isFullWidth = false) => {
    return isFullWidth ? pairFull : pairCell;
};
```

Using a 4-column base with `col-span-2` for pairs avoids conflicting with legacy `.grid-cols-1` and `.grid-cols-2` rules completely.

## Step 3: Apply the Safe Layout Pattern in Components

Import and use the grid tokens across your form views:

`resources/js/views/PropertyLiability/Burglary/Create.vue:`
```vue
<script setup>
import { pairGrid, pairCell, pairFull } from '@/services/property_liability/burglary/pair-grid';
</script>

<template>
    <div :class="pairGrid">
        <!-- Field 1: Half width (2 of 4 columns) -->
        <div :class="pairCell">
            <label class="block text-sm font-medium text-slate-700">Policy Effective Date</label>
            <input type="date" class="w-full mt-1 border rounded px-3 py-2" />
        </div>

        <!-- Field 2: Half width (2 of 4 columns) -->
        <div :class="pairCell">
            <label class="block text-sm font-medium text-slate-700">Expiry Date</label>
            <input type="date" class="w-full mt-1 border rounded px-3 py-2" />
        </div>

        <!-- Field 3: Full width span (4 of 4 columns) -->
        <div :class="pairFull">
            <label class="block text-sm font-medium text-slate-700">Risk Location Address</label>
            <textarea rows="3" class="w-full mt-1 border rounded px-3 py-2"></textarea>
        </div>
    </div>
</template>
```

This layout renders consistently as two equal columns on desktop and scales cleanly without relying on fragile breakpoint overrides.

## What Can Go Wrong

- **Relying Exclusively on Dev Server Previews**: Developers testing exclusively under `npm run dev` miss cascade regressions because Vite injects styles into the `<head>` dynamically. Always verify changes with `npm run build`.
- **Using `!important` Overrides**: Adding `!important` to utility classes creates specificity wars across components and breaks third-party dialogs. Use unique column metrics instead.

## Summary

CSS cascade ordering issues between legacy stylesheets and modern utility frameworks can break production interfaces silently. By testing with compiled production builds and using collision-free grid patterns, teams ensure predictable responsive layouts across all deployment environments.

## Further Reading

- [Vite Backend Integration Guide](https://vitejs.dev/guide/backend-integration.html)
- [Tailwind CSS Cascade Layers](https://tailwindcss.com/docs/adding-custom-styles#using-css-and-layer)
- [MDN CSS Specificity and Cascade Rules](https://developer.mozilla.org/en-US/docs/Web/CSS/Cascade)

Test your application with `npm run build` locally to catch CSS cascade collisions before deploying to production.
