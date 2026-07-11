<?php

namespace Tests\Unit;

use App\Services\Telegram\OidcClient;
use App\Services\Telegram\VerifyLoginWidget;
use Tests\TestCase;

class TelegramAuthTest extends TestCase
{
    public function test_login_widget_verifies_valid_hmac(): void
    {
        $botToken = '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11';
        $authDate = time();
        $data = [
            'id' => '123456789',
            'first_name' => 'John',
            'username' => 'johndoe',
            'auth_date' => (string) $authDate,
        ];

        $dataCheckString = collect($data)->sortKeys()->map(fn ($v, $k) => $k.'='.$v)->implode("\n");
        $secretKey = hash('sha256', $botToken, true);
        $data['hash'] = hash_hmac('sha256', $dataCheckString, $secretKey);

        $service = new VerifyLoginWidget;

        $this->assertTrue($service->verify($data, $botToken));
    }

    public function test_login_widget_rejects_expired_auth_date(): void
    {
        config(['telegramauth.auth_date_max_age' => 300]);

        $botToken = '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11';
        $data = [
            'id' => '123456789',
            'first_name' => 'John',
            'auth_date' => (string) (time() - 400),
        ];

        $dataCheckString = collect($data)->sortKeys()->map(fn ($v, $k) => $k.'='.$v)->implode("\n");
        $secretKey = hash('sha256', $botToken, true);
        $data['hash'] = hash_hmac('sha256', $dataCheckString, $secretKey);

        $service = new VerifyLoginWidget;

        $this->assertFalse($service->verify($data, $botToken));
    }

    public function test_login_widget_rejects_tampered_hash(): void
    {
        $botToken = '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11';
        $data = [
            'id' => '123456789',
            'first_name' => 'John',
            'auth_date' => (string) time(),
            'hash' => 'invalid',
        ];

        $service = new VerifyLoginWidget;

        $this->assertFalse($service->verify($data, $botToken));
    }

    public function test_pkce_code_challenge_roundtrip(): void
    {
        $verifier = OidcClient::generateCodeVerifier();
        $challenge = OidcClient::generateCodeChallenge($verifier);

        $this->assertTrue(OidcClient::verifyCodeChallenge($verifier, $challenge));
        $this->assertFalse(OidcClient::verifyCodeChallenge('wrong-verifier', $challenge));
    }
}
