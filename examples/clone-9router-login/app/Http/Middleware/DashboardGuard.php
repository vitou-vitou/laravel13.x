<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('dashboard.require_login') === false) {
            return $next($request);
        }

        if (session('dashboard_authenticated') === true) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return redirect()->route('login');
    }
}
