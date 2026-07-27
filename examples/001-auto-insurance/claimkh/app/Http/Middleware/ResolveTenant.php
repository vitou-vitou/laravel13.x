<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->extractSubdomain($request);

        if (!$subdomain) {
            return response()->json(['error' => 'tenant_not_found'], 404);
        }

        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            return response()->json(['error' => 'tenant_not_found'], 404);
        }

        $request->attributes->set('tenant', $tenant);
        app()->instance(Tenant::class, $tenant);

        return $next($request);
    }

    private function extractSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            return $parts[0];
        }

        return $request->header('X-Tenant-Subdomain');
    }
}
