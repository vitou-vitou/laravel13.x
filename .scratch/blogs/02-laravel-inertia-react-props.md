# Laravel with Inertia and React: Props Are the Contract, Stop Refetching Client-Side

You open a React page inside an Inertia application and find a `useEffect` hook that fires an Axios request to `/api/orders` on mount. The author built a full React SPA mental model inside a tool designed specifically to eliminate client-side data fetching. The page flashes an empty loading spinner, hits the server twice for every navigation, and duplicates routes across two files.

Inertia links Laravel routing directly to React components. The controller provides the props, and the React component renders them. If you treat Inertia props as your single source of truth, you can delete your client-side fetch libraries, remove loading skeletons from page transitions, and maintain a single set of routes.

## How Inertia Actually Passes Data

When you navigate to an Inertia route, Laravel intercepts the request and sends back a JSON payload containing the component name and its props. On standard browser visits, Laravel returns a full HTML shell with those props encoded in a data attribute.

Your controller remains the sole author of page state:

app/Http/Controllers/OrderController.php:
```php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Orders/Index', [
            'orders' => Order::query()
                ->with('customer:id,name,email')
                ->latest()
                ->paginate(15)
                ->through(fn ($order) => [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'total' => $order->total_formatted,
                    'status' => $order->status,
                    'customer_name' => $order->customer->name,
                ]),
            'filters' => $request->only(['search', 'status']),
        ]);
    }
}
```

The controller shapes the data before it leaves the server. Notice the explicit projection in `through()`: only fields the UI actually needs are sent over the wire. Sensitive attributes like internal notes or customer IDs never reach the browser.

## The Anti-Pattern: The Redundant Mount Effect

Developers coming from Next.js or pure Single Page Applications often reach for `useEffect` out of habit. They render an empty table, mount the component, and immediately trigger an asynchronous API call.

Here is the anti-pattern you should actively delete:

resources/js/Pages/Orders/Index.tsx:
```tsx
// Anti-pattern: do not do this in Inertia
export default function Index() {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios.get('/api/orders').then(response => {
            setOrders(response.data);
            setLoading(false);
        });
    }, []);

    if (loading) return <Spinner />;

    return <OrderTable orders={orders} />;
}
```

This pattern creates three distinct problems. First, it requires maintaining an extra API route and controller alongside your web route. Second, it causes layout shift and flashes loading spinners on every page transition. Third, it breaks browser back-button navigation because the client state must re-fetch from scratch.

Instead, accept props directly at the root of your component:

resources/js/Pages/Orders/Index.tsx:
```tsx
import { Head, Link } from '@inertiajs/react';

interface OrderItem {
    id: number;
    reference: string;
    total: string;
    status: string;
    customer_name: string;
}

interface IndexProps {
    orders: {
        data: OrderItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { search?: string; status?: string };
}

export default function Index({ orders, filters }: IndexProps) {
    return (
        <div className="py-8 max-w-7xl mx-auto px-4">
            <Head title="Orders" />
            <h1 className="text-2xl font-bold mb-6">Customer Orders</h1>
            
            <table className="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th className="px-4 py-2 text-left">Reference</th>
                        <th className="px-4 py-2 text-left">Customer</th>
                        <th className="px-4 py-2 text-left">Total</th>
                        <th className="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {orders.data.map(order => (
                        <tr key={order.id}>
                            <td className="px-4 py-3 font-mono text-sm">{order.reference}</td>
                            <td className="px-4 py-3">{order.customer_name}</td>
                            <td className="px-4 py-3">{order.total}</td>
                            <td className="px-4 py-3">{order.status}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
```

The page renders immediately with data populated. When a user clicks a link to this page, Inertia fetches the page props in the background and swaps the DOM cleanly.

## Handle Form Mutations with the useForm Hook

When updating data, you do not need Axios, custom submit handlers, or manual error state arrays. Inertia provides a dedicated `useForm` hook that tracks form values, handles submission lifecycles, and automatically maps Laravel validation errors to form fields.

Here is an idiomatic edit form in React:

resources/js/Pages/Orders/Edit.tsx:
```tsx
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface EditProps {
    order: {
        id: number;
        status: string;
        notes: string;
    };
}

export default function Edit({ order }: EditProps) {
    const { data, setData, put, processing, errors } = useForm({
        status: order.status,
        notes: order.notes ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(`/orders/${order.id}`);
    };

    return (
        <form onSubmit={submit} className="max-w-xl space-y-4">
            <div>
                <label className="block text-sm font-medium">Status</label>
                <select
                    value={data.status}
                    onChange={e => setData('status', e.target.value)}
                    className="mt-1 block w-full rounded border-gray-300"
                >
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                </select>
                {errors.status && <p className="text-red-500 text-xs mt-1">{errors.status}</p>}
            </div>

            <button
                type="submit"
                disabled={processing}
                className="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50"
            >
                Save Order
            </button>
        </form>
    );
}
```

When the user submits, Inertia makes an HTTP PUT request. If validation fails in your Laravel Form Request, Laravel sends back a 422 redirect and Inertia populates the `errors` object automatically without reloading the browser window.

## Refreshing Data with Partial Reloads

If you only need to refresh one piece of data on the page—such as notification counts or filtered search results—do not re-fetch the entire page. Use Inertia's partial reloads with the `only` option.

Trigger a targeted reload from your component:

resources/js/Pages/Orders/Index.tsx:
```tsx
import { router } from '@inertiajs/react';

function refreshMetrics() {
    router.reload({ only: ['metrics'] });
}
```

Tell Laravel to evaluate that prop only when requested by wrapping it in a closure in the controller:

app/Http/Controllers/OrderController.php:
```php
return Inertia::render('Orders/Index', [
    'orders' => Order::paginate(15),
    'metrics' => fn () => Order::calculateHeavyMetrics(),
]);
```

Because `metrics` is wrapped in a closure, Laravel skips executing the query on standard page visits unless the client specifically requests it in a partial reload.

## What Can Go Wrong

The most dangerous pitfall with Inertia is passing entire Eloquent models directly to the view:

```php
// Dangerous: leaks password hashes, two-factor secrets, and raw keys
return Inertia::render('Users/Show', ['user' => $user]);
```

Any attribute not marked `$hidden` on the model is serialized into JSON and visible in the browser's page source. Always use resource collections, DTOs, or explicit array projections in your controller before passing data to `Inertia::render()`.

## Summary

Inertia eliminates the complexity of separate client-side state management. Treat props received from your Laravel controller as the authoritative contract. Use `useForm` for submissions, lean on server-side Form Requests for validation, and use partial reloads when refreshing heavy metrics.

Stop treating your Inertia pages like a detached SPA. Write clean controllers, pass structured props, and let the framework handle the wire.

## Further Reading

- [Inertia.js React Adapter](https://inertiajs.com/client-side-setup#react)
- [Inertia.js Forms and useForm](https://inertiajs.com/forms#form-helper)
- [Inertia.js Partial Reloads](https://inertiajs.com/partial-reloads)
- [Laravel Eloquent API Resources](https://laravel.com/docs/eloquent-resources)

How is your team structuring Inertia props and TypeScript interfaces? Let us know in the comments below.
