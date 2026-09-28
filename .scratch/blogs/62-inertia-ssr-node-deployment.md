# Server-Side Rendering (SSR) with Inertia: SEO and Performance on Production Linux

You build a modern, interactive e-commerce catalog or marketing portal using Laravel and Inertia with Vue or React. To users, the application feels fast and responsive. But when you inspect the raw HTML returned to search engine crawlers like Googlebot or social media scrapers on Twitter and LinkedIn, the page source is a completely empty `<div id="app"></div>` shell. Social share previews fail to display product images, and organic search rankings begin dropping because web crawlers struggle to index client-side JavaScript applications reliably.

Single Page Applications render on the client by default, which can harm search engine discoverability and First Contentful Paint (FCP) metrics. You do not need to rewrite your application in Next.js or Nuxt to get the benefits of server-rendered HTML. Inertia provides first-class support for **Server-Side Rendering (SSR)** directly within your Laravel project. If you compile an SSR bundle using Vite and supervise a lightweight local Node.js rendering process on your Linux server, you can serve fully pre-rendered HTML to crawlers and users alike while retaining the developer experience of a Laravel monolith.

## How Inertia SSR Operates

In a standard Inertia application, Laravel returns a minimal HTML layout containing an empty `div` and a data-page JSON attribute:

```html
<!-- Standard client-side Inertia response: empty shell -->
<div id="app" data-page="{&quot;component&quot;:&quot;Products/Show&quot;,...}"></div>
```

The browser downloads `app.js`, parses the JavaScript, and renders the HTML elements in the client DOM.

With Inertia SSR enabled, the request lifecycle changes:

```
[User / Googlebot] ---> (GET /products/42) ---> [Laravel Controller]
                                                         |
                                 1. Passes props to local Node.js SSR daemon (port 13714)
                                 2. Node pre-renders Vue/React component into raw HTML string
                                 3. Returns raw HTML back to Laravel (takes ~15ms)
                                                         |
[User / Googlebot] <--- [Fully Pre-Rendered HTML Page with Headings, Text & Images]
```

When Googlebot or a social share crawler scrapes the page, every heading, paragraph, image tag, and meta tag is already present in the initial HTTP response body.

## Step 1: Enable SSR in Vite and Inertia

Update your `vite.config.js` to specify the SSR entry point:

vite.config.js:
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            ssr: 'resources/js/ssr.js', // Dedicated SSR entry point
            refresh: true,
        }),
        vue(),
    ],
});
```

Create the SSR entry point in `resources/js/ssr.js`:

resources/js/ssr.js:
```js
import { createSSRApp, h } from 'vue';
import { renderToString } from '@vue/server-renderer';
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: (name) =>
            resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue')
            ),
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) }).use(plugin);
        },
    })
);
```

Enable SSR in `config/inertia.php`:

config/inertia.php:
```php
return [
    'ssr' => [
        'enabled' => true,
        'url' => 'http://127.0.0.1:13714', // Local Node.js SSR daemon port
    ],
];
```

## Step 2: Build and Test SSR Locally

Build your production assets, including the SSR bundle:

```bash
npm run build
```

Vite compiles both your client-side assets in `public/build/assets/` and a server bundle in `bootstrap/ssr/ssr.js`.

Start the local SSR server daemon:

```bash
php artisan inertia:start-ssr
```

Open a terminal and make a test curl request to your local application:

```bash
curl http://localhost:8000/products/1 | grep "<h1"
```

You will see the fully pre-rendered `<h1>` tag and product details directly in the raw cURL output.

## Step 3: Run the SSR Daemon in Production with Supervisor

In production, the SSR server must run continuously alongside your PHP-FPM workers. Configure Supervisor to keep `inertia:start-ssr` alive:

/etc/supervisor/conf.d/inertia-ssr.conf:
```ini
[program:inertia-ssr]
command=php /var/www/app/artisan inertia:start-ssr
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/app/storage/logs/inertia-ssr.log
stopwaitsecs=10
```

Add an SSR restart command to your production deployment script:

deploy.sh:
```bash
# Production Deployment Script
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan inertia:stop-ssr
sudo supervisorctl restart inertia-ssr
php artisan optimize
```

Stopping and restarting the SSR process ensures that the Node server loads the newly compiled JavaScript components into memory.

## What Can Go Wrong

The most frequent bug when adopting SSR is using browser-only APIs (like `window`, `document`, or `localStorage`) in top-level component setup code:

```js
// Fatal SSR Error: 'window is not defined' inside Node.js!
const token = localStorage.getItem('token');
const width = window.innerWidth;
```

Because the SSR daemon runs inside Node.js on the server where no browser window exists, referencing `window` at the root of a script setup block will crash the renderer.

To safely use browser APIs, move them into the `onMounted()` hook:

```vue
<script setup>
import { onMounted, ref } from 'vue';

const width = ref(1024);

onMounted(() => {
    // onMounted ONLY executes in real web browsers, NEVER during server-side rendering
    width.value = window.innerWidth;
});
</script>
```

Code inside `onMounted()` executes exclusively on real user devices, keeping your Node SSR process crash-free.

## Summary

You do not need to abandon Laravel for a separate JavaScript framework to achieve first-class SEO and instant First Contentful Paint metrics.

Configure Inertia Server-Side Rendering. Build your SSR bundle with Vite, supervise the lightweight local Node daemon with Supervisor, and keep browser-only APIs isolated inside `onMounted()` lifecycle hooks.

Your search engine rankings improve, social share cards display rich previews, and your users experience pre-rendered pages with zero blank loading screens.

## Further Reading

- [Inertia.js Server-Side Rendering (SSR) Guide](https://inertiajs.com/server-side-rendering)
- [Vue 3 Server-Side Rendering Overview](https://vuejs.org/guide/scaling-up/ssr.html)
- [Google Search Central: Understanding JavaScript SEO](https://developers.google.com/search/docs/crawling-indexing/javascript/javascript-seo-basics)
- [Core Web Vitals: First Contentful Paint (FCP)](https://web.dev/articles/fcp)

Are you running Inertia SSR in production? Share your server memory footprints and performance benchmarks in the comments below.
