<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request, string $locale): RedirectResponse
    {
        $allowed = config('app.supported_locales', ['en']);

        if (! in_array($locale, $allowed, true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        return redirect()->back();
    }
}
