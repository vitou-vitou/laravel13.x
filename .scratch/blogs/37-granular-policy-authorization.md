# Beyond Role Checkboxes: How to Structure Maintainable Laravel Policies

Your company starts with two roles: `Admin` and `User`. Over two years, sales asks for `Manager`, support asks for `Operator`, and compliance asks for `Auditor`. Your database ends up with a matrix of forty-eight boolean permission flags (`can_refund`, `can_export_csv`, `can_override_discount`). You find yourself writing `@if(auth()->user()->is_admin || (auth()->user()->role === 'manager' && $order->total < 500))` across twelve different Blade views and controller actions.

Role checkboxes scale poorly because business permissions rarely depend solely on job titles. They depend on resource ownership, monetary thresholds, record lifecycle states, and organizational boundaries. If you move authorization logic out of templates and controllers and encapsulate it inside dedicated Laravel Policy classes, you create a unified, testable security boundary that protects your application across web requests, API calls, and console commands.

## The Pitfall of Template-Level Gatekeeping

When authorization logic is scattered throughout the application, controllers and Blade templates become tangled:

```php
// Anti-pattern: business authorization rules hardcoded into controllers
public function refund(Order $order)
{
    // Hardcoded logic duplicated across controllers, API routes, and views!
    if ($order->status !== 'completed') {
        abort(400, 'Only completed orders can be refunded.');
    }

    if (! auth()->user()->hasRole('manager') && $order->total_amount > 1000) {
        abort(403, 'Refunds over $1,000 require manager approval.');
    }

    $order->processRefund();
}
```

This approach creates serious vulnerabilities:
- The UI might hide the "Refund" button, but if an operator knows the URL or sends a forged POST request, the action executes because the controller forgot the check.
- If refund rules change from $1,000 to $500, developers must search and replace across multiple files, invariably missing one.

## Encapsulate Rules in Laravel Policies

Generate a dedicated policy mapped to your Eloquent model using Artisan:

```bash
php artisan make:policy OrderPolicy --model=Order
```

Define granular, domain-driven authorization rules inside the policy:

app/Policies/OrderPolicy.php:
```php
namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->hasRole(['admin', 'support']);
    }

    /**
     * Determine whether the user can process a refund on the order.
     */
    public function refund(User $user, Order $order): Response
    {
        // 1. Lifecycle state validation
        if ($order->status !== 'completed') {
            return Response::deny('Only completed orders can be refunded.');
        }

        // 2. High-value refunds require specific managerial authority
        if ($order->total_amount_cents > 100000 && ! $user->hasRole('finance_manager')) {
            return Response::deny('Refunds exceeding $1,000 require finance manager authorization.');
        }

        // 3. Standard staff authorization
        return $user->hasRole(['admin', 'finance_manager', 'support_tier_2'])
            ? Response::allow()
            : Response::deny('You do not have permission to refund orders.');
    }
}
```

Notice the return type: `Illuminate\Auth\Access\Response`. Using `Response::deny('Reason')` allows the policy to communicate exact business reasons back to the user when authorization is rejected.

## Authorize at the Controller Gate

With rules isolated inside the policy, your controller action becomes two clean lines:

app/Http/Controllers/OrderRefundController.php:
```php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderRefundController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        // Enforces OrderPolicy::refund() automatically
        $this->authorize('refund', $order);

        $order->processRefund();

        return redirect()->route('orders.show', $order)
            ->with('status', 'Refund processed successfully.');
    }
}
```

If the user lacks permission or the order is not in a completed state, Laravel halts execution immediately and returns an HTTP 403 Forbidden with the exact denial message.

## Check Permissions in Blade and Frontend Templates

In Blade views, use the `@can` and `@cannot` directives to conditionally render buttons based on the exact same policy:

resources/views/orders/show.blade.php:
```blade
<div class="flex justify-between items-center py-4">
    <h1 class="text-xl font-bold">Order #{{ $order->reference }}</h1>

    @can('refund', $order)
        <button type="button" class="btn btn-danger" onclick="openRefundModal()">
            Refund Order
        </button>
    @endcan
</div>
```

The UI button displays only when the user has both the appropriate role and the order meets all required business conditions.

## What Can Go Wrong

A frequent trap is using the `before()` method on a policy to grant super-admin bypasses without restriction:

```php
// Dangerous: blanket super-admin bypass ignores business invariants
public function before(User $user, string $ability): ?bool
{
    if ($user->isSuperAdmin()) {
        return true; // Overrides state checks: super-admin can refund a draft order!
    }
    return null;
}
```

If your `before()` method blindly returns `true`, a super-admin will bypass lifecycle rules—such as trying to refund an order that was never paid or editing an archived tax invoice.

Use `before()` only to grant role-based permission, never to bypass state machine checks. Keep business state validation inside the method itself or return `null` from `before()`.

## Summary

Stop accumulating dozens of boolean role flags in your database and embedding permission checks in Blade templates.

Centralize authorization logic in dedicated Laravel Policies. Encapsulate business rules, ownership checks, and lifecycle conditions inside policy methods, and enforce them at the controller boundary with `$this->authorize()`.

Your security rules remain testable, your controllers stay thin, and your user interface stays in perfect sync with backend authorization.

## Further Reading

- [Laravel Documentation: Writing Policies](https://laravel.com/docs/authorization#writing-policies)
- [Laravel Policy Responses and Custom Error Messages](https://laravel.com/docs/authorization#policy-responses)
- [Role-Based Access Control (RBAC) vs Attribute-Based Access Control (ABAC)](https://en.wikipedia.org/wiki/Attribute-based_access_control)
- [Martin Fowler: Gateway and Authorization Patterns](https://martinfowler.com/articles/gateway-pattern.html)

How do you organize policy logic when permission rules depend on complex tenant settings? Share your architecture in the comments below.
