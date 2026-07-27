<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OtpRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function requestOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+855[0-9]{8,9}$/'],
        ]);

        $phone = $request->phone;
        $key = 'otp:'.$phone;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $retryAfter = RateLimiter::availableIn($key);
            return response()->json([
                'error' => 'Too many OTP requests',
                'retry_after' => $retryAfter,
            ], 429)->withHeaders(['Retry-After' => $retryAfter]);
        }

        RateLimiter::hit($key, 600);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(5);

        OtpRequest::create([
            'phone' => $phone,
            'otp_hash' => Hash::make($otp),
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        // TODO: dispatch SMS job via Vonage/Twilio
        // SendOtpSms::dispatch($phone, $otp);

        return response()->json(['expires_at' => $expiresAt->toISOString()]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
            'tenant_subdomain' => ['required', 'string'],
        ]);

        $tenant = Tenant::where('subdomain', $request->tenant_subdomain)
            ->where('is_active', true)
            ->firstOrFail();

        $otpRecord = OtpRequest::where('phone', $request->phone)
            ->whereNull('used_at')
            ->orderByDesc('created_at')
            ->first();

        if (!$otpRecord) {
            throw ValidationException::withMessages(['otp' => 'invalid_otp']);
        }

        if ($otpRecord->isExpired()) {
            throw ValidationException::withMessages(['otp' => 'expired_otp']);
        }

        if (!Hash::check($request->otp, $otpRecord->otp_hash)) {
            throw ValidationException::withMessages(['otp' => 'invalid_otp']);
        }

        $otpRecord->update(['used_at' => now()]);

        $user = User::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => $request->phone],
            ['role' => 'claimant', 'is_active' => true]
        );

        if (!$user->is_active) {
            return response()->json(['error' => 'account_inactive'], 403);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'phone' => $user->phone,
                'name' => $user->name,
                'role' => $user->role,
                'tenant_id' => $user->tenant_id,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'logged_out']);
    }
}
