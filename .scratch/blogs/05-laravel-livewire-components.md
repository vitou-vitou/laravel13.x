# Livewire Components: Keep State on the Server, Stop Building Hidden Service Containers

You open a Livewire component and find public Eloquent model properties serialized into the HTML payload, third-party payment calls running inside `render()`, and methods like `deleteUser()` exposed with zero authorization checks. Livewire made full-stack reactivity so fast to write that the component absorbed an entire application's worth of business logic.

Livewire components are server-side entry points, not private classes. Every public property is sent to the client and back on every network roundtrip, and every public method can be triggered by any browser that can forge an HTTP request. If you treat components as lightweight presentation controllers and keep your public state minimal, Livewire applications remain secure, fast, and maintainable.

## The Danger of Public Eloquent Models

In older tutorials, developers frequently attached entire Eloquent models directly to public component properties:

app/Livewire/EditUser.php:
```php
// Anti-pattern: do not expose raw Eloquent models as public properties
class EditUser extends Component
{
    public User $user; // Serialized to the client DOM on every request!
}
```

When Livewire serializes an Eloquent model to the browser, it includes model attributes in the encrypted snapshot. If your model contains columns like `is_admin`, `internal_notes`, or password resets, that data is pushed to the client and re-hydrated on every request. Even worse, if you modify attributes carelessly, users can tamper with inputs to mutate fields you never intended to expose.

Use typed scalar properties or Livewire's Form Objects instead:

app/Livewire/Forms/UserForm.php:
```php
namespace App\Livewire\Forms;

use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UserForm extends Form
{
    public ?User $user;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email')]
    public string $email = '';

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function update(): void
    {
        $this->validate();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);
    }
}
```

A Form Object isolates form validation and attributes from the component lifecycle. Only `name` and `email` travel over the wire, protecting your database structure.

## Always Authorize Public Actions

A Livewire component's public methods are reachable by any user who opens the browser console. If you have a method named `delete()`, anyone can trigger it using standard Livewire JavaScript calls unless you explicitly enforce authorization.

Never assume that hiding a button in Blade protects the action:

app/Livewire/OrderManagement.php:
```php
namespace App\Livewire;

use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class OrderManagement extends Component
{
    use AuthorizesRequests;

    public function cancelOrder(Order $order): void
    {
        // Enforce policy authorization on every public action
        $this->authorize('cancel', $order);

        $order->update(['status' => 'cancelled']);

        session()->flash('message', 'Order cancelled successfully.');
    }

    public function render()
    {
        return view('livewire.order-management', [
            'orders' => Order::latest()->paginate(10),
        ]);
    }
}
```

By calling `$this->authorize('cancel', $order)`, Livewire delegates to your standard Laravel Policy. If an unauthorized user attempts to fire `wire:click="cancelOrder(42)"`, Laravel immediately returns a 403 Forbidden response.

## Don't Let Components Become Service Containers

Because Livewire makes it trivial to bind buttons to PHP methods, components quickly become dump sites for heavy external operations:

```php
// Anti-pattern: placing synchronous external API calls in a component
public function generateInvoice()
{
    $pdf = $this->pdfGenerator->buildHugePdf($this->order);
    $this->stripe->chargeCustomer($this->order);
    Mail::to($this->order->customer)->send(new InvoiceMail($pdf));
}
```

If Stripe takes two seconds to respond or PDF generation takes four seconds, the user's browser hangs on a loading state. If the connection drops, you risk charging the card without sending the invoice.

Extract heavy work to queued background jobs:

app/Livewire/InvoiceComponent.php:
```php
namespace App\Livewire;

use App\Jobs\ProcessOrderInvoiceJob;
use App\Models\Order;
use Livewire\Component;

class InvoiceComponent extends Component
{
    public function requestInvoice(Order $order): void
    {
        $this->authorize('view', $order);

        ProcessOrderInvoiceJob::dispatch($order, auth()->user());

        $this->dispatch('notify', 'Your invoice is being generated and will be emailed shortly.');
    }
}
```

The component triggers the job, dispatches a browser event to show a notification toast, and frees the user to keep working.

## What Can Go Wrong

A frequent performance issue in Livewire is running expensive database queries inside computed properties or loop re-renders without caching or pagination.

If your component renders a list of items with relationships:

resources/views/livewire/user-list.blade.php:
```blade
<div>
    @foreach ($users as $user)
        <div>{{ $user->name }} - {{ $user->department->name }}</div>
    @endforeach
</div>
```

Ensure your `render()` method eager-loads relationships:

```php
public function render()
{
    return view('livewire.user-list', [
        'users' => User::with('department')->paginate(20),
    ]);
}
```

If you omit `with('department')`, every row in your Livewire table executes an individual query every time any property in the component updates.

## Summary

Livewire is one of the most productive tools in the Laravel ecosystem when used with care. Keep public properties strictly scoped to necessary inputs using Form Objects. Authorize every public action with Laravel Policies, and delegate long-running tasks like emails and external APIs to queued jobs.

Treat your Livewire components like presentation layers, not backend monopolies. Your components will run faster, stay secure, and remain clean enough for your entire team to understand.

## Further Reading

- [Livewire Form Objects Documentation](https://livewire.laravel.com/docs/forms#extracting-a-form-object)
- [Livewire Security Guidelines](https://livewire.laravel.com/docs/security)
- [Laravel Authorization Policies](https://laravel.com/docs/authorization#creating-policies)
- [Livewire Component Lifecycle](https://livewire.laravel.com/docs/lifecycle-hooks)

Have you transitioned large components to Livewire Form Objects? Let us know your favorite patterns in the comments.
