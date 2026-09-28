# Realtime Laravel with Reverb: Run WebSockets Without External Cloud Services

You need to add real-time features to your application: live chat messages, real-time order status updates, and collaborative dashboard counters. Your team looks at commercial hosted WebSocket solutions like Pusher or Ably. At 50 concurrent connections during testing, everything seems affordable. But as your user base expands to 5,000 concurrent connections, monthly bills quickly escalate into hundreds of dollars per month, and compliance teams flag customer data passing through third-party WebSocket infrastructure.

In Laravel 11 and later, you do not need commercial third-party services to run high-throughput WebSockets. **Laravel Reverb** is a first-party, high-performance WebSocket server written specifically for the Laravel ecosystem. Built directly into the framework and optimized for async I/O via Revolt, Reverb can comfortably manage tens of thousands of concurrent WebSocket connections on a single inexpensive VPS. If you configure Reverb with Laravel Echo, you can deliver sub-millisecond real-time updates while keeping your data and hosting costs completely under your control.

## Why Self-Hosted WebSockets Used to Be Hard

Historically, running self-hosted WebSockets in PHP was notoriously painful:
- Running Node.js socket servers (like Socket.io) beside PHP required maintaining a second programming language runtime and managing IPC authentication bridges.
- Older PHP socket libraries (like Ratchet) suffered from single-threaded memory bottlenecks and lacked native integration with Laravel event broadcasting.

Laravel Reverb solves this by operating as a native Artisan daemon (`php artisan reverb:start`) that integrates with Laravel's built-in event broadcasting system and Redis pub/sub backplanes.

## Step 1: Install and Configure Reverb

Install Reverb via Artisan:

```bash
php artisan install:broadcasting
```

This installs Reverb, configures `config/reverb.php`, updates your broadcasting connection to `reverb`, and creates sample channel routes in `routes/channels.php`.

Your `.env` file is populated with local Reverb server credentials:

.env:
```ini
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME="http"

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

In production, run Reverb behind your Nginx reverse proxy with SSL termination so `REVERB_SCHEME` is `https` and the port is standard 443.

## Step 2: Create a Broadcastable Event

Create an event that implements the `ShouldBroadcast` interface:

app/Events/OrderStatusUpdatedEvent.php:
```php
namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order) {}

    /**
     * Broadcast on a private channel scoped to the customer.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel("orders.{$this->order->customer_id}");
    }

    /**
     * The payload name sent to frontend listeners.
     */
    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    /**
     * The exact data delivered over the WebSocket connection.
     */
    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'reference' => $this->order->reference,
            'status' => $this->order->status,
            'updated_at' => $this->order->updated_at->toISOString(),
        ];
    }
}
```

Notice `broadcastWith()`: only send the explicit data the frontend needs. Never broadcast raw internal Eloquent models that expose un-contracted database columns over public WebSockets.

## Step 3: Authorize the Private Channel

In `routes/channels.php`, define an authorization callback to verify that the connecting user owns the channel:

routes/channels.php:
```php
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('orders.{customerId}', function (User $user, int $customerId) {
    // Only allow the customer themselves or staff members to listen to this channel
    return (int) $user->id === (int) $customerId || $user->hasRole(['admin', 'support']);
});
```

When the client connects via Laravel Echo, Reverb calls this authorization rule over HTTP to guarantee privacy.

## Step 4: Listen for Real-Time Events with Laravel Echo

Configure Laravel Echo on the frontend:

resources/js/echo.js:
```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

Now, listen for events inside your Vue or React component:

resources/js/Pages/Orders/Show.vue:
```vue
<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps(['order', 'customerId']);
const currentStatus = ref(props.order.status);

onMounted(() => {
    // Listen to the authenticated private channel
    window.Echo.private(`orders.${props.customerId}`)
        .listen('.OrderStatusUpdated', (event) => {
            if (event.order_id === props.order.id) {
                currentStatus.value = event.status;
            }
        });
});

onUnmounted(() => {
    // Clean up channel listener when leaving the page
    window.Echo.leave(`orders.${props.customerId}`);
});
</script>

<template>
    <div class="p-6">
        <h1 class="text-xl font-bold">Order #{{ order.reference }}</h1>
        <div class="mt-4 p-4 border rounded bg-white">
            Status: <span class="font-bold text-emerald-600">{{ currentStatus }}</span>
        </div>
    </div>
</template>
```

When an order status changes on your server, Reverb broadcasts the update to the user's browser in under ten milliseconds without a page refresh.

## Running Reverb in Production under Supervisor

Reverb runs as a persistent daemon process. Configure Supervisor to keep it running:

/etc/supervisor/conf.d/reverb.conf:
```ini
[program:reverb]
command=php /var/www/app/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/app/storage/logs/reverb.log
stopwaitsecs=30
```

Configure Nginx to proxy `/app` WebSocket connections to port 8080 with `Upgrade` and `Connection` headers set.

## What Can Go Wrong

A frequent issue when scaling Reverb across multiple server instances is **message isolation**.

If Server A broadcasts an event, but a customer's WebSocket connection is held by Server B, Server B will never know about the event unless a shared pub/sub backplane exists.

In multi-server deployments, configure Redis as your Reverb scaling driver in `config/reverb.php`:

config/reverb.php:
```php
'servers' => [
    'reverb' => [
        // ...
        'scaling' => [
            'enabled' => true,
            'channel' => 'reverb',
            'server' => [
                'connection' => 'default',
            ],
        ],
    ],
],
```

Redis distributes broadcast events across all Reverb server instances instantly.

## Summary

Stop paying expensive per-connection subscription fees to third-party WebSocket vendors.

Deploy Laravel Reverb to power your real-time architecture. Run Reverb as an Artisan daemon under Supervisor, broadcast clean payloads using `ShouldBroadcast`, authorize private channels with `routes/channels.php`, and listen via Laravel Echo.

You gain complete control over your real-time infrastructure, reduce latency, and scale to thousands of concurrent users with zero third-party bills.

## Further Reading

- [Laravel Reverb Official Documentation](https://laravel.com/docs/reverb)
- [Laravel Event Broadcasting Guide](https://laravel.com/docs/broadcasting)
- [Revolt PHP: Async Fiber Engine](https://revolt.run/)
- [Nginx WebSocket Proxying Configuration](https://nginx.org/en/docs/http/websocket.html)

How many concurrent WebSocket connections does your real-time application support? Share your Reverb deployment experiences in the comments below.
