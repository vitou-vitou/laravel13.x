<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CookieConsentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $minutes = 60 * 24 * 365;

        return redirect()->back()->withCookie(cookie(
            'cookie_consent',
            '1',
            $minutes,
            '/',
            null,
            (bool) config('session.secure', false),
            true,
            false,
            config('session.same_site') ?? 'lax'
        ));
    }
}
