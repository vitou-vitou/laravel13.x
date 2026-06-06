<?php

namespace App\Rules;

use App\Models\Tunnel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;

class ValidNgrokDomain implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('Enter a public ngrok hostname.');

            return;
        }

        try {
            Tunnel::validateDomain(Tunnel::normalizeDomain($value));
        } catch (ValidationException $exception) {
            $message = $exception->validator->errors()->first('domain');

            $fail(is_string($message) ? $message : 'Invalid ngrok domain.');
        }
    }
}
