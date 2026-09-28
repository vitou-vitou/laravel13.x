# Asset Versioning and Cloudflare CDN: Zero-Downtime Cache Busting on Every Deploy

You deploy a major frontend redesign on your production Laravel application. Five minutes after deployment, customer support receives dozens of complaints: login buttons are broken, layouts are distorted, and JavaScript console errors report `TypeError: e.renderProfile is not a function`. You purge your Cloudflare CDN cache, but users whose browsers cached the old `app.js` file continue experiencing broken layouts for hours because their local browsers refuse to check the network for updated files.

Caching static assets aggressively at the CDN edge and in user browsers is essential for sub-second page performance. But without disciplined asset versioning, aggressive caching turns deployments into outages. If you leverage Vite's content-hash filename hashing in Laravel and configure Cloudflare with immutable cache-control headers, you can cache static assets for an entire year with zero risk of stale asset collisions during deployments.

## The Flaw of Query-String Versioning

Historically, developers attempted to bust browser caches using query strings:

```html
<!-- Anti-pattern: query-string cache busting -->
<script src="/js/app.js?v=2.1.4"></script>
```

Query-string cache busting is fundamentally unreliable:
1. **CDN Caching Proxies Ignore Query Strings:** Many corporate network proxies, older browser caches, and custom CDN rules ignore query strings by default, serving stale cached versions of `app.js` regardless of `?v=`.
2. **Atomic Rollback Failures:** If you need to roll back a deployment, reverting to the previous release can break if intermediate assets were overwritten in place on disk.

## Content-Hashed Filenames: The Modern Standard

Modern Vite asset compilation in Laravel solves cache busting by calculating a cryptographic SHA-256 hash of the file's *contents* and embedding that hash directly into the filename:

```
public/build/assets/app-C9kZ8q1a.js
public/build/assets/app-B3xY4m9p.css
```

Notice what happens:
- If `app.js` does not change, its filename remains `app-C9kZ8q1a.js`.
- If you change a single character in your Vue, React, or CSS code, Vite generates a completely new filename: `app-F7wR2t5k.js`.
- The old file and the new file can exist simultaneously on disk and across CDN edge servers without collision.

## Step 1: Configure Immutable Cache Headers in Nginx

Because content-hashed filenames represent immutable versions of code that will never change, instruct web browsers and Cloudflare to cache them aggressively for one full year.

Configure your Nginx server block to set `Cache-Control: public, max-age=31536000, immutable` on all files under `/build/`:

/etc/nginx/sites-available/app.conf:
```nginx
server {
    server_name app.example.com;
    root /var/www/app/public;

    # Immutable caching for Vite compiled assets (1 Year)
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, max-age=31536000, immutable";
        access_log off;
        try_files $uri =404;
    }

    # Dynamic HTML entry points must NEVER be cached aggressively
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
}
```

Look at the rule distinction:
- **Compiled Assets (`/build/assets/*`):** Cached in browser and Cloudflare edge memory for 1 year (`max-age=31536000, immutable`). The browser will *never* make an HTTP request to re-validate these files.
- **HTML Document (`/`):** Delivered dynamically by Laravel with standard `Cache-Control: no-cache, private`. Every time a user opens a page, Laravel returns fresh HTML containing the updated script tags pointing to the new hashed filenames.

## Step 2: Configure Cloudflare Edge Rules

In your Cloudflare dashboard, create a **Cache Rule** for your compiled asset path:

```
If Incoming Request matches:
URI Path starts with "/build/"

Then apply settings:
Edge Cache TTL: 1 month
Browser Cache TTL: 1 year
Cache Level: Cache Everything
```

Cloudflare edge nodes around the world cache your compiled JavaScript, CSS, and images. Over 95% of your static asset traffic is absorbed by Cloudflare's edge network, reducing your primary server bandwidth and CPU to near zero.

## Step 3: Zero-Downtime Atomic Deployments

During a deployment, a user might load the HTML shell for Version 1 right before your deployment finishes, but request the JavaScript chunks for Version 1 three seconds later after Version 2 was compiled.

If your deployment process wipes the `public/build/` directory with `rm -rf`, that user's browser receives a 404 error when requesting Version 1's JavaScript chunks.

To achieve true zero-downtime deployments:

1. **Deploy with Symlinks (Deployer, Envoy, or Capistrano):** Keep the previous release directory on disk.
2. **Retain Historical Build Assets:** Configure your build script to retain build artifacts from the last 2 releases in `public/build/assets/`.

Here is an Envoy or bash deployment script pattern:

deploy.sh:
```bash
# Compile new assets in a fresh release folder
cd /var/www/releases/2026-09-27-001
npm ci
npm run build

# Switch the live symlink atomically
ln -sfn /var/www/releases/2026-09-27-001 /var/www/current
sudo systemctl reload php8.3-fpm
```

The switch occurs atomically in single-digit microseconds. Users with existing sessions can continue loading Version 1 assets from the previous release folder, while new requests receive Version 2.

## What Can Go Wrong

The most frequent bug occurs when non-hashed assets (such as a generic `public/logo.png` or `favicon.ico`) are served from the root `public/` directory without content hashes.

If you update `logo.png` without changing its filename, Cloudflare and user browsers will continue serving the old logo for thirty days.

Always manage images and fonts through Vite:

```blade
<!-- Vite automatically hashes assets imported in templates! -->
<img src="{{ Vite::asset('resources/images/logo.png') }}" alt="Logo">
```

Vite compiles the image to `public/build/assets/logo-D4mK8p.png`, guaranteeing immediate cache invalidation whenever the image file changes.

## Summary

Never rely on query-string parameters (`?v=1.2`) to manage asset caching.

Adopt content-hashed filenames via Laravel Vite. Serve compiled assets with `Cache-Control: public, max-age=31536000, immutable`, cache them aggressively across Cloudflare's global edge network, and deploy releases atomically using symlinks.

Your static asset latency drops to near zero, bandwidth costs disappear, and every deployment transitions smoothly without a single stale cache collision.

## Further Reading

- [Laravel Documentation: Compiling Assets with Vite](https://laravel.com/docs/vite)
- [MDN Web Docs: Cache-Control Immutable](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control#immutable)
- [Cloudflare Documentation: Edge and Browser Cache TTL](https://developers.cloudflare.com/cache/how-to/set-caching-levels/)
- [Deployer: Atomic Symlink Deployments for PHP](https://deployer.org/)

How do you manage asset caching and CDN invalidation during continuous deployments? Share your infrastructure setup in the comments below.
