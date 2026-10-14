<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardAuthController extends Controller
{
    public function __construct(
        private readonly LoginLimiter $limiter
    ) {}

    public function showLogin(Request $request)
    {
        if (session('dashboard_authenticated') === true) {
            return redirect()->route('dashboard');
        }

        return view('auth.login', [
            'hasPassword' => ! empty(config('dashboard.password')),
            'resetHint' => config('dashboard.reset_hint'),
        ]);
    }

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        $ip = $request->ip() ?? 'unknown';
        $lock = $this->limiter->checkLock($ip);
        $resetHint = config('dashboard.reset_hint');

        if ($lock['locked']) {
            $message = "Too many failed attempts. Try again in {$lock['retryAfter']}s. {$resetHint}";

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $message,
                    'retryAfter' => $lock['retryAfter'],
                    'resetHint' => $resetHint,
                ], 429, ['Retry-After' => (string) $lock['retryAfter']]);
            }

            return back()->withErrors(['password' => $message])
                ->with('retryAfter', $lock['retryAfter'])
                ->with('resetHint', $resetHint);
        }

        $password = (string) $request->input('password');
        $storedHash = config('dashboard.password');
        $initialPassword = config('dashboard.initial_password', '123456');

        $isValid = ! empty($storedHash)
            ? Hash::check($password, $storedHash)
            : $password === $initialPassword;

        if ($isValid) {
            $this->limiter->recordSuccess($ip);

            $isDefaultPassword = empty($storedHash) && (empty(env('INITIAL_PASSWORD')) || env('INITIAL_PASSWORD') === '123456');
            if ($isDefaultPassword && ! $this->isLocalRequest($request)) {
                $errorMsg = 'Default password must be changed before remote access. Change it from the local machine (or set INITIAL_PASSWORD).';

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => $errorMsg,
                        'mustChangePassword' => true,
                    ], 403);
                }

                return back()->withErrors(['password' => $errorMsg])->with('mustChangePassword', true);
            }

            session(['dashboard_authenticated' => true]);
            $request->session()->regenerate();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'mustChangePassword' => false,
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        $failResult = $this->limiter->recordFail($ip);
        $postLock = $this->limiter->checkLock($ip);

        if ($postLock['locked']) {
            $message = "Too many failed attempts. Try again in {$postLock['retryAfter']}s. {$resetHint}";

            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $message,
                    'retryAfter' => $postLock['retryAfter'],
                    'resetHint' => $resetHint,
                ], 429, ['Retry-After' => (string) $postLock['retryAfter']]);
            }

            return back()->withErrors(['password' => $message])
                ->with('retryAfter', $postLock['retryAfter'])
                ->with('resetHint', $resetHint);
        }

        $remaining = $failResult['remainingBeforeLock'];
        $message = "Invalid password. {$remaining} attempt(s) left before lockout.";

        if ($request->wantsJson()) {
            return response()->json([
                'error' => $message,
                'remainingBeforeLock' => $remaining,
            ], 401);
        }

        return back()->withErrors(['password' => $message])
            ->with('remainingBeforeLock', $remaining);
    }

    public function status(Request $request): JsonResponse
    {
        $authenticated = session('dashboard_authenticated') === true;
        $requireLogin = (bool) config('dashboard.require_login', true);

        return response()->json([
            'requireLogin' => $requireLogin,
            'authMode' => config('dashboard.auth_mode', 'password'),
            'hasPassword' => ! empty(config('dashboard.password')),
            'authenticated' => $authenticated,
            'displayName' => 'Password user',
            'loginMethod' => 'Password',
        ]);
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        session()->forget('dashboard_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('login');
    }

    private function isLocalRequest(Request $request): bool
    {
        $ip = $request->ip();

        return in_array($ip, ['127.0.0.1', '::1', 'localhost'], true);
    }
}
