---
title: "How to Build a Backend-For-Frontend Shell with Laravel and Vue"
published: true
description: "Structure a secure Backend-For-Frontend architecture using Laravel session cookies and a Vue admin interface over remote microservices."
tags: "laravel, vue, bff, architecture, security"
canonical_url: "https://github.com/vitou/laravel13.x/blob/main/.scratch/blogs/80-laravel-vue-bff-architecture.md"
---

When modernizing legacy enterprise systems, teams frequently debate between rewriting monolithic backends or building client-side Single Page Applications (SPAs) that communicate directly with internal microservices. Exposing raw microservice APIs directly to browser JavaScript leaks sensitive service contracts, introduces CORS friction, and makes credential management hazardous.

The Backend-For-Frontend (BFF) pattern resolves this by inserting a thin, secure gateway layer. In this architecture, a Laravel session shell orchestrates authentication, while a Vue admin application handles complex domain views. Let's see how.

## The Problem: The Vulnerable Client-Direct SPA

Connecting browser SPAs directly to internal REST endpoints forces the client to manage access tokens, refresh tokens, and granular permission states:

```text
Browser SPA (stores JWT in localStorage) ──> [Remote API Gateway] ──> Microservices
```

If a user falls victim to cross-site scripting (XSS), attackers can extract the stored bearer token and impersonate the user across all internal APIs. Furthermore, client-side applications must handle raw downstream errors, service timeouts, and protocol translations.

A Laravel BFF layer keeps tokens out of browser storage by terminating authentication at the web server boundary.

## Architecture: The Laravel BFF Session Boundary

In a Laravel BFF setup, the browser only ever talks to Laravel through encrypted, `HttpOnly`, `SameSite=Lax` session cookies. Laravel securely holds the remote API credentials in server-side session storage or memory:

```text
Browser (Vue App) ──[Encrypted Session Cookie]──> Laravel BFF ──[Bearer Token / Mutual TLS]──> Remote PAI Engine
```

The browser never touches the remote upstream bearer token, eliminating the risk of client-side token exfiltration.

## Step 1: Establish the Proxy Gateway Service

Create an internal gateway service in Laravel that proxies authenticated requests to downstream APIs while attaching backend credentials:

`app/Services/PaiGatewayService.php:`
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Client\Response;

class PaiGatewayService
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey
    ) {}

    public function forward(string $method, string $endpoint, array $data = []): Response
    {
        $token = Session::get('remote_pai_token');

        return Http::baseUrl($this->baseUrl)
            ->withToken($token)
            ->withHeaders([
                'X-Gateway-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->send($method, $endpoint, [
                'json' => $data,
            ]);
    }
}
```

The downstream microservice validates the gateway's server-to-server token, while frontend clients simply interact with standard Laravel web routes.

## Step 2: Configure Route Handlers and Session Bridges

Define clean controller actions that wrap downstream responses without exposing raw internal error dumps:

`app/Http/Controllers/Api/QuotationProxyController.php:`
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaiGatewayService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuotationProxyController extends Controller
{
    public function __construct(
        protected PaiGatewayService $gateway
    ) {}

    public function show(string $id): JsonResponse
    {
        $response = $this->gateway->forward('GET', "/api/v1/quotations/{$id}");

        if ($response->failed()) {
            return response()->json([
                'message' => 'Unable to retrieve quotation data.',
            ], $response->status());
        }

        return response()->json($response->json());
    }
}
```

This controller sanitizes downstream errors, logs internal diagnostic details locally, and returns safe payloads to the Vue frontend.

## Step 3: Consume Endpoints via Typed Vue Services

On the frontend, Vue services call local relative paths without worrying about API keys or OAuth token exchanges:

`resources/js/services/quotation.service.js:`
```javascript
import axios from 'axios';

export const quotationService = {
    async getQuotation(id) {
        try {
            const { data } = await axios.get(`/api/quotations/${id}`);
            return data;
        } catch (error) {
            const status = error.response?.status;
            const message = error.response?.data?.message || 'Failed to load quotation';
            throw new Error(`[${status}] ${message}`);
        }
    },
};
```

Axios automatically passes the CSRF token and session cookies generated by Laravel on every request.

## What Can Go Wrong

- **Leaking Downstream Stack Traces**: When internal services return an HTTP 500 with raw database query dumps, proxying those responses directly to the client exposes infrastructure details. Always catch and transform failed responses.
- **Session Timeout Mismatch**: If Laravel's web session lasts 120 minutes but the downstream service token expires after 15 minutes, API requests fail while the web session appears active. Implement token refresh logic within your gateway service.

## Summary

The Backend-For-Frontend architecture gives enterprise teams the speed of modern Vue component development while keeping authentication tokens secure inside Laravel. Centralizing upstream requests through a dedicated gateway eliminates CORS complexity and protects internal service contracts.

## Further Reading

- [Microsoft Architecture Guide: Backend-For-Frontend Pattern](https://learn.microsoft.com/en-us/azure/architecture/patterns/backends-for-frontends)
- [Laravel HTTP Client Documentation](https://laravel.com/docs/http-client)
- [OWASP Session Management Security](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)

Adopt a Laravel BFF gateway layer to decouple your frontend user experience from internal microservice credentials.
