<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\Auth\AuthEngine;
use App\Services\Auth\TokenIssuer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function __invoke(Request $request, AuthEngine $authEngine): JsonResponse
    {
        $validated = $request->validate([
            'grant_type' => ['required', 'in:authorization_code'],
            'code' => ['required', 'string'],
            'redirect_uri' => ['required', 'url'],
            'client_id' => ['required', 'string'],
            'client_secret' => ['required', 'string'],
            'code_verifier' => ['required', 'string'],
        ]);

        $application = Application::query()
            ->where('client_id', $validated['client_id'])
            ->where('is_active', true)
            ->first();

        if (! $application || ! \Illuminate\Support\Facades\Hash::check($validated['client_secret'], $application->client_secret)) {
            return response()->json(['error' => 'invalid_client'], 401);
        }

        $tokens = $authEngine->exchangeAuthCode(
            $application,
            $validated['code'],
            $validated['code_verifier'],
            $validated['redirect_uri']
        );

        if (! $tokens) {
            return response()->json(['error' => 'invalid_grant'], 400);
        }

        return response()->json($tokens);
    }
}
