<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('app.supported_locales', ['en']);

        $locale = $request->session()->get('locale');

        if (is_string($locale) && in_array($locale, $allowed, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
