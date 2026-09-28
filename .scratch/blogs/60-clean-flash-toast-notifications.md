# Session Flash to UI Toast: Build a Seamless Notification Pipeline

You perform a database update in a controller and redirect back with a status message: `return redirect()->back()->with('status', 'Profile saved successfully.')`. In classic Blade applications, an alert box renders at the top of the template. But as your team migrates screens to Inertia with Vue or React, developers start duplicating notification code: half of the notifications are fired by manual Axios promises, while the other half rely on full-page reloads, resulting in missed messages, desynchronized alert banners, and inconsistent UI toasts.

Communication between server-side redirects and client-side notifications should follow a single, predictable pipeline. In modern hybrid architectures, Laravel's session flash is the single source of truth for mutation side effects. If you share session flash messages globally through Inertia middleware and capture them with a dedicated client-side Toast watcher, you can trigger beautiful, animated notifications automatically on every redirect without writing repetitive frontend alert code.

## The Flaw of Fragmented Toast Logic

When notification logic is fragmented across frontend components, code looks like this:

```vue
<!-- Anti-pattern: writing manual toast triggers in every single form -->
<script setup>
import { useToast } from 'vue-toastification';

const toast = useToast();

function submit() {
    form.put('/profile', {
        onSuccess: () => toast.success('Profile saved!'),
        onError: () => toast.error('Something failed!'),
    });
}
</script>
```

This pattern creates three major problems:
- **Repetitive Boilerplate:** Every single form across fifty Vue or React components must import the toast library, configure timeouts, and manually handle `onSuccess`.
- **Backend Bypassing:** When actions are triggered from standard server redirects, queued jobs, or external webhooks, the frontend never knows to show the notification.
- **Inconsistent Messaging:** Flash text hardcoded in Vue diverges from messages returned by standard Laravel Form Requests.

## Step 1: Share Session Flash via HandleInertiaRequests Middleware

Inertia provides a dedicated middleware, `HandleInertiaRequests`, that shares server-side data with all frontend page components automatically.

Update `app/Http/Middleware/HandleInertiaRequests.php` to share flash payloads:

app/Http/Middleware/HandleInertiaRequests.php:
```php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            // Share structured flash notifications globally
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
```

Notice the use of closures (`fn () => ...`). Wrapping flash access in a closure guarantees that Laravel only inspects the session when rendering the response, keeping performance optimal.

## Step 2: Flash Messages Cleanly in Controllers

Now, your Laravel controllers flash messages using standard framework idioms:

app/Http/Controllers/ProfileController.php:
```php
namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        // Simple, clean redirect with a flash key
        return redirect()->route('profile.edit')
            ->with('success', 'Your profile details have been saved.');
    }
}
```

You do not need to construct complex JSON responses. Standard Laravel redirects handle the message.

## Step 3: Global Toast Pipeline in Vue / React

In your root layout or application entry point, watch the Inertia `page.props.flash` object and trigger your notification library:

resources/js/Layouts/AppLayout.vue:
```vue
<script setup>
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const page = usePage();

// Automatically watch for flash updates on every Inertia navigation
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;

        if (flash.success) {
            toast.success(flash.success);
        }
        if (flash.error) {
            toast.error(flash.error);
        }
        if (flash.warning) {
            toast.warning(flash.warning);
        }
        if (flash.info) {
            toast.info(flash.info);
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <slot />
    </div>
</template>
```

Whenever an Inertia visit completes after a server redirect, the watcher detects the updated `page.props.flash` object and automatically renders a beautiful animated toast.

Individual form components do not need a single line of toast code.

## Handling Modals and Dialog Auto-Closes

When closing modals upon successful form submission, combine Inertia form callbacks with flash detection:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['close']);
const form = useForm({ name: '' });

function submit() {
    form.post('/categories', {
        onSuccess: () => {
            // Modal closes cleanly; toast triggers automatically via global watcher!
            emit('close');
            form.reset();
        },
    });
}
</script>
```

The modal closes immediately, and the success toast appears at the top right of the viewport.

## What Can Go Wrong

A frequent bug with session flash notifications in Single Page Applications is **duplicate toast firings** on browser back-button navigation.

When a user clicks the browser's Back button, Inertia restores the previous page state from its client-side cache, which might still contain the old `flash.success` string.

To prevent duplicate toast notifications:
- In `vue3-toastify` or `react-toastify`, set `toastId` to the message string or a timestamp:

```js
toast.success(flash.success, {
    toastId: flash.success, // Prevents identical toasts from displaying twice
});
```

The toast library automatically deduplicates identical messages within the active display window.

## Summary

Do not scatter manual notification calls across dozens of frontend components.

Establish a unified notification pipeline. Share session flash messages globally through `HandleInertiaRequests`, trigger redirects with `->with('success', '...')` in your Laravel controllers, and capture updates in a centralized layout watcher on the frontend.

Your codebase stays clean, notifications remain consistent across all screens, and your developers can ship new forms without writing repetitive toast plumbing.

## Further Reading

- [Inertia.js Shared Data Documentation](https://inertiajs.com/shared-data)
- [Laravel Session Flash Data](https://laravel.com/docs/session#flash-data)
- [Vue3 Toastify Official Documentation](https://vue3-toastify.js-bridge.com/)
- [Designing User-Friendly Notification Systems](https://www.nngroup.com/articles/indicators-validations-notifications/)

How does your team coordinate server validation errors with client-side toast notifications? Share your tips in the comments below.
