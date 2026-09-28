# Single-Source Validation: Share Laravel Rules with Frontend Forms Cleanly

You build a registration form with complex validation: password complexity, matching confirmations, email formats, and minimum age requirements. You write complete validation rules inside a Laravel `FormRequest`. Then, because the product manager wants instant inline feedback as users type, a frontend engineer duplicates all thirty lines of validation rules in TypeScript using Zod, Yup, or manual regex functions. Three months later, marketing decides to reduce the password length requirement from 12 characters to 8 characters. The backend rule is updated, but the frontend library is forgotten, causing the form to reject valid registrations on the client before the request ever reaches the server.

Maintaining dual validation layers creates synchronization debt and subtle client-side bugs. The backend server must always remain the authoritative source of truth for validation rules. If you generate client-side validation rules dynamically from your Laravel Form Requests or expose rule metadata over a shared contract, you provide instantaneous frontend validation feedback while maintaining a single, unified source of truth.

## The Flaw of Duplicate Validation Rules

When validation is authored in two places:

```php
// Backend: app/Http/Requests/RegisterRequest.php
public function rules(): array
{
    return [
        'password' => ['required', 'string', 'min:12', 'regex:/[A-Z]/'],
    ];
}
```

```ts
// Frontend: resources/js/Pages/Register.vue
// Duplicated regex and constraints in TypeScript!
const schema = z.object({
    password: z.string().min(12).regex(/[A-Z]/),
});
```

The friction multiplies across your project:
- Changes to database column sizes (`max:255`) require updates in two different programming languages.
- Regular expression dialects differ between PHP (PCRE) and JavaScript (ECMAScript), causing strings that pass client validation to fail on the server, or vice versa.
- Developers spend valuable time writing and testing the exact same business constraints twice.

## How to Share Validation Rules Idiomatically

There are two primary architectural patterns to share validation rules cleanly:

1. **Pre-Compiling Form Requests to TypeScript:** Using CLI tools during asset compilation to export Form Requests into typed client schemas.
2. **Dynamic Inertia Form Error Binding:** Letting Laravel validate on submission or debounced blur events and populating Inertia's native `form.errors` object automatically.

## Pattern 1: Automatic Schema Generation via Precognition

Laravel includes a powerful native feature for live frontend validation: **Laravel Precognition**.

Precognition allows frontend forms to query your backend `FormRequest` rules in real time *before* submitting the form, executing the exact backend validation rules without triggering database mutations or side effects:

app/Http/Controllers/Auth/RegisterController.php:
```php
namespace App\Http\Controllers\Auth;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Precognition automatically intercepts live validation checks here!
        User::create($request->validated());

        return redirect()->route('dashboard');
    }
}
```

On the frontend, use the official `@laravel/precognition-vue` or `@laravel/precognition-react` adapter:

resources/js/Pages/Register.vue:
```vue
<script setup>
import { useForm } from 'laravel-precognition-vue';

// The form knows the exact Laravel route and queries backend rules dynamically
const form = useForm('post', route('register'), {
    name: '',
    email: '',
    password: '',
});

function submit() {
    form.submit();
}
</script>

<template>
    <form @submit.prevent="submit" class="max-w-md space-y-4">
        <div>
            <label class="block text-sm font-medium">Email Address</label>
            <input
                v-model="form.email"
                @change="form.validate('email')"
                type="email"
                class="w-full border rounded p-2"
            />
            <!-- Real-time backend validation error displayed as the user types! -->
            <p v-if="form.invalid('email')" class="text-red-500 text-xs mt-1">
                {{ form.errors.email }}
            </p>
        </div>

        <div>
            <label class="block text-sm font-medium">Password</label>
            <input
                v-model="form.password"
                @change="form.validate('password')"
                type="password"
                class="w-full border rounded p-2"
            />
            <p v-if="form.invalid('password')" class="text-red-500 text-xs mt-1">
                {{ form.errors.password }}
            </p>
        </div>

        <button :disabled="form.processing" type="submit" class="btn btn-primary">
            Register Account
        </button>
    </form>
</template>
```

Look at the architectural benefits:
- **Zero Frontend Validation Code:** You did not write a single line of Zod, Yup, or regex on the client.
- **Identical Rules:** The client's live inline errors are generated directly by your Laravel `RegisterRequest`.
- **Database Unique Checks:** Precognition can even check `unique:users,email` in real time, alerting the user that an email is already taken before they submit the form.

## Pattern 2: Exporting Rule Metadata for Offline Forms

If your application operates in offline environments (such as field service tablets or progressive web apps), query-based validation like Precognition cannot reach the server.

In this scenario, use a package like `spatie/laravel-validation-to-json` or export rule arrays to JSON fixtures during your asset build step:

```php
// Convert FormRequest rules to portable JSON constraints
$rules = (new RegisterRequest())->rules();
file_put_contents(resource_path('js/contracts/register-rules.json'), json_encode($rules));
```

A lightweight TypeScript helper parses basic constraints (`required`, `min:8`, `email`) on the client while treating the backend as the final authority.

## What Can Go Wrong

A frequent pitfall with live validation like Precognition is triggering HTTP requests on every single keystroke.

If a user types a 20-character password quickly, firing twenty concurrent network requests will overwhelm your server and cause race conditions in the UI.

Always debounce live validation checks:

```vue
<!-- Debounce validation by 300ms or validate only on blur/change -->
<input
    v-model="form.password"
    @input="form.validate('password', { debounce: 300 })"
    type="password"
/>
```

Debouncing ensures that validation executes only when the user pauses typing, keeping server traffic minimal.

## Summary

Stop authoring and maintaining duplicate validation schemas across PHP and TypeScript.

Adopt a single-source validation strategy. Keep your business rules centralized in Laravel `FormRequest` classes, and use Laravel Precognition to deliver real-time inline feedback to your Vue, React, or Alpine forms.

You eliminate synchronization bugs, reduce frontend bundle sizes, and guarantee that client feedback is always a perfect reflection of your backend business rules.

## Further Reading

- [Laravel Documentation: Laravel Precognition](https://laravel.com/docs/precognition)
- [Laravel Form Request Validation](https://laravel.com/docs/validation#form-request-validation)
- [Precognition Vue Adapter Guide](https://github.com/laravel/precognition-vue)
- [Don't Repeat Yourself (DRY) in Multi-Tier Architectures](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself)

How do you handle client-side form validation across complex multi-step wizards? Share your architecture in the comments below.
