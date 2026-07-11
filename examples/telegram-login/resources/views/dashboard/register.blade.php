<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — TelegramAuth</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f1f5f9; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        form { background: #fff; padding: 2rem; border-radius: 10px; width: 100%; max-width: 420px; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        label { display: block; margin-top: 1rem; font-weight: 600; font-size: .875rem; }
        input { width: 100%; padding: .625rem; margin-top: .25rem; border: 1px solid #cbd5e1; border-radius: 6px; }
        button { margin-top: 1.5rem; width: 100%; padding: .75rem; background: #2563eb; color: #fff; border: 0; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .error { color: #dc2626; font-size: .875rem; margin-top: .5rem; }
    </style>
</head>
<body>
    <form method="POST" action="{{ route('dashboard.register') }}">
        @csrf
        <h1>Create your company account</h1>
        <label>Company name<input type="text" name="company_name" value="{{ old('company_name') }}" required></label>
        <label>Your name<input type="text" name="name" value="{{ old('name') }}" required></label>
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required></label>
        @error('email')<div class="error">{{ $message }}</div>@enderror
        <label>Password<input type="password" name="password" required></label>
        <label>Confirm password<input type="password" name="password_confirmation" required></label>
        <button type="submit">Register</button>
    </form>
</body>
</html>
