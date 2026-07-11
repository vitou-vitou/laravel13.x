<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthSession;
use App\Services\Auth\AuthEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WidgetCallbackController extends Controller
{
    public function __invoke(Request $request, AuthEngine $authEngine): RedirectResponse
    {
        $session = AuthSession::query()
            ->where('state', $request->query('state'))
            ->where('flow', 'widget')
            ->with('application.telegramBot')
            ->firstOrFail();

        $telegramData = $request->only([
            'id', 'first_name', 'last_name', 'username', 'photo_url', 'auth_date', 'hash',
        ]);

        $redirectUrl = $authEngine->completeWidgetLogin($session, $telegramData, $request);

        if (! $redirectUrl) {
            abort(403, 'Telegram authentication failed');
        }

        return redirect()->away($redirectUrl);
    }
}
