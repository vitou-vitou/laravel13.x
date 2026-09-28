# Avoid Production Tailwind Traps: Why Dynamic Class Names Vanish on Build

You build a status badge component in your local development environment using Vue or Blade. You write code that dynamically constructs background colors based on order status: `bg-${status}-500`. During local development with `npm run dev`, everything looks gorgeous: completed orders display emerald green badges, pending orders display yellow badges, and cancelled orders display crimson red badges. You push the code to production, run `npm run build`, and all the badges turn into transparent, unstyled gray boxes.

Tailwind CSS does not run a runtime CSS interpreter in the browser. During production compilation, Tailwind scans your project files as raw text strings looking for complete, unbroken class names to extract into the final CSS stylesheet. If you construct class names using string concatenation or dynamic interpolation, Tailwind's regex parser cannot see them, and those classes are permanently omitted from your production build.

## How Tailwind Scans Your Source Code

Understanding Tailwind's extraction mechanism explains why dynamic classes fail:

1. **Static Regex Scanning:** Tailwind does not evaluate PHP, execute JavaScript, or interpret Vue template variables. It reads your `.blade.php`, `.vue`, and `.js` files using regular expressions looking for class strings.
2. **Purging Unused Rules:** If a CSS class name (e.g. `bg-emerald-500`) does not appear as an exact, unbroken string somewhere in your project files, Tailwind assumes it is unused and excludes it from `public/build/assets/app.css` to keep the stylesheet small.

During local development with Vite Hot Module Replacement (HMR), Tailwind might inject styles on the fly or retain previously extracted classes in memory. The bug only surfaces in production when running a clean `npm run build`.

## The Fatal Anti-Pattern: String Interpolation

Here is the code pattern that breaks in production:

```vue
<!-- Anti-pattern: Tailwind CANNOT extract dynamically constructed class names -->
<template>
    <span :class="`bg-${statusColor}-100 text-${statusColor}-800`">
        {{ status }}
    </span>
</template>

<script setup>
const props = defineProps(['status']);

// Returns 'emerald', 'amber', or 'rose'
const statusColor = computed(() => {
    return props.status === 'paid' ? 'emerald' : 'amber';
});
</script>
```

When Tailwind scans this file, it finds `bg-${statusColor}-100`. It does not know what `${statusColor}` will be at runtime. Consequently, neither `bg-emerald-100` nor `bg-amber-100` is generated in the production CSS file.

## The Solution: Complete Class Mapping Dictionaries

Always write **complete, unbroken class names** in your components. Use an explicit dictionary object or lookup map:

resources/js/Components/StatusBadge.vue:
```vue
<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

// Explicit dictionary containing complete, unbroken Tailwind class strings
const statusClasses = {
    paid: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    pending: 'bg-amber-100 text-amber-800 border-amber-200',
    shipped: 'bg-blue-100 text-blue-800 border-blue-200',
    refunded: 'bg-rose-100 text-rose-800 border-rose-200',
};

const badgeClass = computed(() => {
    return statusClasses[props.status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
});
</script>

<template>
    <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium border', badgeClass]">
        {{ status }}
    </span>
</template>
```

When Tailwind scans this file, its parser reads the literal strings `bg-emerald-100`, `text-emerald-800`, `bg-rose-100`, etc. Every required utility rule is extracted into the production build.

## In Blade: Use Match Expressions with Literal Classes

If you write standard Blade views, use PHP 8's `match` expression with complete class strings:

resources/views/components/status-badge.blade.php:
```blade
@props(['status'])

@php
$classes = match($status) {
    'completed', 'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
    'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
    'failed', 'cancelled' => 'bg-rose-100 text-rose-800 border-rose-300',
    default => 'bg-gray-100 text-gray-800 border-gray-300',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {$classes}"]) }}>
    {{ ucfirst($status) }}
</span>
```

Tailwind's scanner finds every class literal inside the `match` block, ensuring that production styling renders identically to local development.

## The Safelist Escape Hatch

If you build a Content Management System (CMS) or user-customizable dashboard where colors are pulled dynamically from a database (e.g. `$tenant->theme_color` where users pick arbitrary Tailwind colors), Tailwind cannot find those classes in your source files.

In this specific scenario, configure the `safelist` in `tailwind.config.js`:

tailwind.config.js:
```js
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    // Force Tailwind to compile these classes even if they do not appear in templates
    safelist: [
        {
            pattern: /bg-(red|green|blue|amber|emerald)-(100|500|800)/,
        },
        {
            pattern: /text-(red|green|blue|amber|emerald)-(100|800)/,
        },
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
```

The `safelist` pattern tells Tailwind to compile all specified color variations into the final stylesheet regardless of whether they appear in template files.

## What Can Go Wrong

A dangerous mistake is overusing the `safelist` pattern.

If you safelist `pattern: /bg-.*-500/`, Tailwind will generate background classes for every single color shade across the entire palette. Your production CSS file will balloon from 15KB to over 500KB, degrading page performance for all users.

Use `safelist` only when dynamic database values strictly require it. For application UI components, explicit dictionary lookups are always smaller, cleaner, and more maintainable.

## Summary

Never construct Tailwind class names dynamically using string concatenation or template literals.

Understand that Tailwind extracts classes through static text scanning, not runtime evaluation. Write complete, unbroken class names in dictionary objects or PHP `match` expressions, and reserve `safelist` strictly for dynamic database-driven themes.

Your styles will compile reliably on production builds, eliminating mysterious missing styles on deployment day.

## Further Reading

- [Tailwind CSS Documentation: Content Configuration & Dynamic Classes](https://tailwindcss.com/docs/content-configuration#dynamic-class-names)
- [Tailwind CSS Safelisting Classes](https://tailwindcss.com/docs/content-configuration#safelisting-classes)
- [Vite with Tailwind CSS Setup in Laravel](https://laravel.com/docs/vite#configuring-tailwind)
- [CSS Bundle Optimization Strategies](https://web.dev/articles/defer-non-critical-css)

Have you encountered missing Tailwind styles in production builds? Share how you fixed them in the comments below.
