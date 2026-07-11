<?php

namespace App\Services\Auth;

use App\Models\Application;
use App\Models\AuthAuditLog;
use App\Models\AuthCode;
use App\Models\AuthSession;
use App\Models\EndUser;
use App\Models\TenantEndUser;
use App\Services\Telegram\OidcClient;
use App\Services\Telegram\VerifyLoginWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthEngine
{
    public function __construct(
        private VerifyLoginWidget $verifyLoginWidget,
        private TokenIssuer $tokenIssuer,
    ) {}

    public function createSession(
        Application $application,
        string $redirectUri,
        string $codeChallenge,
        string $flow = 'widget'
    ): AuthSession {
        return AuthSession::query()->create([
            'application_id' => $application->id,
            'state' => Str::random(40),
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            'redirect_uri' => $redirectUri,
            'flow' => $flow,
            'expires_at' => now()->addSeconds((int) config('telegramauth.auth_session_ttl', 900)),
        ]);
    }

    public function completeOidcLogin(AuthSession $session, array $profile, Request $request): ?string
    {
        $application = $session->application;

        if ($session->isExpired() || $session->isConsumed()) {
            $this->audit($application->id, 'oidc', false, 'invalid_session', $request);

            return null;
        }

        $endUser = $this->upsertEndUser($profile);
        $this->linkTenantUser($application, $endUser);

        $code = $this->issueAuthCode($session, $endUser);
        $session->update(['consumed_at' => now()]);

        $this->audit($application->id, 'oidc', true, null, $request, $endUser->telegram_id);

        return $this->buildRedirectUrl($session->redirect_uri, [
            'code' => $code,
            'state' => $session->state,
        ]);
    }

    public function completeWidgetLogin(AuthSession $session, array $telegramData, Request $request): ?string
    {
        $application = $session->application()->with('telegramBot')->first();

        if (! $application?->telegramBot) {
            $this->audit($application?->id, 'widget', false, 'missing_bot', $request);

            return null;
        }

        if ($session->isExpired() || $session->isConsumed()) {
            $this->audit($application->id, 'widget', false, 'invalid_session', $request);

            return null;
        }

        if (! $this->verifyLoginWidget->verify($telegramData, $application->telegramBot->bot_token)) {
            $this->audit($application->id, 'widget', false, 'invalid_signature', $request, (int) ($telegramData['id'] ?? 0));

            return null;
        }

        $profile = $this->verifyLoginWidget->extractProfile($telegramData);
        $endUser = $this->upsertEndUser($profile);
        $this->linkTenantUser($application, $endUser);

        $code = $this->issueAuthCode($session, $endUser);

        $session->update(['consumed_at' => now()]);

        $this->audit($application->id, 'widget', true, null, $request, $endUser->telegram_id);

        return $this->buildRedirectUrl($session->redirect_uri, [
            'code' => $code,
            'state' => $session->state,
        ]);
    }

    public function exchangeAuthCode(
        Application $application,
        string $code,
        string $codeVerifier,
        string $redirectUri
    ): ?array {
        $authCode = AuthCode::query()
            ->where('code', $code)
            ->whereHas('authSession', fn ($query) => $query
                ->where('application_id', $application->id)
                ->where('redirect_uri', $redirectUri))
            ->with(['authSession', 'endUser'])
            ->first();

        if (! $authCode || $authCode->isExpired() || $authCode->isUsed()) {
            return null;
        }

        if (! OidcClient::verifyCodeChallenge($codeVerifier, $authCode->authSession->code_challenge)) {
            return null;
        }

        $authCode->update(['used_at' => now()]);

        $endUser = $authCode->endUser;
        $accessToken = $this->tokenIssuer->issueAccessToken($application, $endUser);
        $refreshToken = $this->tokenIssuer->issueRefreshToken();

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('telegramauth.access_token_ttl', 3600),
            'refresh_token' => $refreshToken,
            'user' => $endUser->toProfileArray(),
        ];
    }

    public function audit(
        ?int $applicationId,
        string $flow,
        bool $success,
        ?string $failureReason,
        Request $request,
        ?int $telegramId = null
    ): void {
        AuthAuditLog::query()->create([
            'application_id' => $applicationId,
            'flow' => $flow,
            'success' => $success,
            'failure_reason' => $failureReason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'telegram_id' => $telegramId,
        ]);
    }

    private function upsertEndUser(array $profile): EndUser
    {
        return EndUser::query()->updateOrCreate(
            ['telegram_id' => $profile['telegram_id']],
            [
                'username' => $profile['username'],
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'],
                'photo_url' => $profile['photo_url'],
                'phone' => $profile['phone'],
            ]
        );
    }

    private function linkTenantUser(Application $application, EndUser $endUser): void
    {
        TenantEndUser::query()->updateOrCreate(
            [
                'application_id' => $application->id,
                'end_user_id' => $endUser->id,
            ],
            ['last_login_at' => now()]
        );
    }

    private function issueAuthCode(AuthSession $session, EndUser $endUser): string
    {
        $code = Str::random(48);

        AuthCode::query()->create([
            'auth_session_id' => $session->id,
            'end_user_id' => $endUser->id,
            'code' => $code,
            'expires_at' => now()->addSeconds((int) config('telegramauth.auth_code_ttl', 600)),
        ]);

        return $code;
    }

    private function buildRedirectUrl(string $redirectUri, array $params): string
    {
        $separator = str_contains($redirectUri, '?') ? '&' : '?';

        return $redirectUri.$separator.http_build_query($params);
    }
}
