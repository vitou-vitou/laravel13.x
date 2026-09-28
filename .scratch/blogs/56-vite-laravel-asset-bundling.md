# Optimize Vite in Laravel: Code Splitting and Vendor Chunk Isolation

Your Laravel application boots quickly on the server, but when users open your customer portal on mobile devices or slower connections, the browser hangs on a blank screen for four seconds. You open Chrome DevTools and discover that your Vite build output generated a single monolithic `app.js` bundle weighing 2.8 megabytes. Your marketing landing page, which only requires fifteen lines of vanilla JavaScript for a mobile dropdown menu, is forcing the browser to download and parse the entire Vue or React runtime, Chart.js, Lodash, and a heavy date-picker library.

Modern frontend builds in Laravel rely on Vite by default. But without explicit code-splitting and chunking configuration, Vite will bundle all imported dependencies into a single output file. If you configure manual chunking in `vite.config.js`, leverage dynamic imports for heavy components, and isolate third-party vendor libraries, you can reduce initial page payload sizes by eighty percent and achieve instantaneous page loads.

## The Problem with the Monolithic Bundle

When an application bundles all scripts together:

```js
// Anti-pattern: importing heavy libraries at the top of your main entry point
import './bootstrap';
import Chart from 'chart.js/auto';
import flatpickr from 'flatpickr';
import { createApp } from 'vue';
```

Vite compiles every single dependency into `public/build/assets/app.js`.

This creates severe performance penalties:
- **Cache Invalidation:** If you fix a one-character typo in your application code, browsers must re-download the entire 2.8MB bundle, including third-party libraries that never changed.
- **CPU Parse Overhead:** Mobile browsers must parse and compile megabytes of JavaScript before rendering the Document Object Model (DOM), directly hurting your Interaction to Next Paint (INP) and Largest Contentful Paint (LCP) Core Web Vitals.

## Step 1: Configure Manual Chunk Splitting

Rollup (Vite's underlying production bundler) provides a `manualChunks` option that separates third-party dependencies from your application business logic.

Update `vite.config.js`:

vite.config.js:
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
    build: {
        rollupOptions: {
            output: {
                // Isolate heavy vendor packages into dedicated, long-cacheable chunks
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('chart.js')) {
                            return 'vendor-charts';
                        }
                        if (id.includes('lodash') || id.includes('axios')) {
                            return 'vendor-core';
                        }
                        if (id.includes('vue') || id.includes('@inertiajs')) {
                            return 'vendor-framework';
                        }
                        return 'vendor-misc';
                    }
                },
            },
        },
        chunkSizeWarningLimit: 600, // Warn if any single chunk exceeds 600KB
    },
});
```

When you run `npm run build`, Vite separates the output into distinct files:
- `assets/vendor-framework-[hash].js` (Vue & Inertia runtime: ~85KB)
- `assets/vendor-charts-[hash].js` (Chart.js: ~180KB)
- `assets/app-[hash].js` (Your application code: ~35KB)

When you deploy a code change next week, users only download the 35KB `app.js` file. The heavy vendor chunks remain cached in their browsers for months.

## Step 2: Lazy Load Heavy Components with Dynamic Imports

Do not import heavy components or charting libraries statically on pages where they are not immediately needed. Use JavaScript dynamic imports (`import()`) to load code on demand:

resources/js/Pages/Dashboard.vue:
```vue
<script setup>
import { ref, defineAsyncComponent } from 'vue';

// Standard components load immediately
import StatCard from '@/Components/StatCard.vue';

// Heavy analytics chart loads ONLY when rendered
const RevenueChart = defineAsyncComponent(() =>
    import('@/Components/RevenueChart.vue')
);

const showAnalytics = ref(false);
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <StatCard title="Total Revenue" value="$45,200" />

        <button @click="showAnalytics = true" class="btn btn-primary mt-4">
            Load Deep Analytics
        </button>

        <!-- Chart.js chunk is downloaded over the network ONLY when button is clicked -->
        <div v-if="showAnalytics" class="mt-6">
            <RevenueChart />
        </div>
    </div>
</template>
```

When a user visits the dashboard, the browser downloads only the lightweight HTML shell and `StatCard`. The 180KB chart chunk is fetched over the network only if the user clicks "Load Deep Analytics".

## Step 3: Split CSS by Entry Point

A common bug in Laravel applications is importing administrative CSS into the main public stylesheet:

```css
/* Anti-pattern: app.css importing admin and datatable styling */
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';
@import 'flatpickr/dist/flatpickr.css';
@import './admin-dashboard.css';
```

Separate public marketing CSS from authenticated dashboard CSS by defining multiple input entry points:

vite.config.js:
```js
input: [
    'resources/css/app.css',       // Public marketing styling (~15KB)
    'resources/css/admin.css',     // Heavy admin & portal styling (~120KB)
    'resources/js/app.js',
    'resources/js/admin.js',
],
```

Load only the necessary stylesheet in your Blade layouts:

resources/views/layouts/guest.blade.php:
```blade
<head>
    <!-- Loads only the 15KB stylesheet for public visitors -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

## What Can Go Wrong

A frequent pitfall with aggressive manual chunking is creating **circular dependency cycles** between split vendor files.

If `vendor-charts` imports a helper from `vendor-core`, and `vendor-core` imports an object from `vendor-charts`, the browser can fail to initialize modules with `ReferenceError: Cannot access 'X' before initialization`.

To avoid circular dependencies:
- Keep framework runtimes (Vue, React) together with their official adapters.
- Group by package name rather than arbitrary file paths.
- Test your production build locally by running `npm run build && php artisan serve` before pushing to staging.

## Summary

Do not let unoptimized frontend asset bundles slow down your Laravel application.

Configure Rollup `manualChunks` in `vite.config.js` to isolate vendor libraries from application code. Use dynamic imports (`defineAsyncComponent` or dynamic `import()`) to lazy-load heavy modules on demand, and maintain separate CSS entry points for public and administrative views.

Your initial page load payloads shrink dramatically, browser caching becomes effective, and your users experience instantaneous navigation.

## Further Reading

- [Laravel Documentation: Compiling Assets with Vite](https://laravel.com/docs/vite)
- [Vite Official Guide: Building for Production](https://vitejs.dev/guide/build.html#chunking-strategy)
- [Web Vitals: Largest Contentful Paint (LCP) Optimization](https://web.dev/articles/optimize-lcp)
- [Rollup Documentation: output.manualChunks](https://rollupjs.org/configuration-options/#output-manualchunks)

What is the bundle size of your Laravel application's production assets today? Share your optimization metrics in the comments below.
