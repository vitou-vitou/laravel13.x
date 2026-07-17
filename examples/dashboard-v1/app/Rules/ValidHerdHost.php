<?php

namespace App\Rules;

use App\Models\Tunnel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;

class ValidHerdHost implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            $fail('Enter a Herd hostname (e.g. dashboard-v1.test).');

            return;
        }

        try {
            Tunnel::validateHerdHost($value);
        } catch (ValidationException $exception) {
            $message = $exception->validator->errors()->first('herd_host');

            $fail(is_string($message) ? $message : 'Invalid Herd host.');
        }
    }
}
