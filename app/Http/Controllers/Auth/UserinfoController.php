<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TokenIssuer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserinfoController extends Controller
{
    public function __invoke(Request $request, TokenIssuer $tokenIssuer): JsonResponse
    {
        $authorization = $request->header('Authorization', '');

        if (! str_starts_with($authorization, 'Bearer ')) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        $token = substr($authorization, 7);
        $claims = $tokenIssuer->decodeAccessToken($token);

        if (! $claims || ! isset($claims['user'])) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        return response()->json($claims['user']);
    }
}
