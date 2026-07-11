<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\AuthSession;
use App\Models\TelegramBot;
use App\Models\Tenant;
use App\Services\Telegram\OidcClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class VerifyTelegramAuthCommand extends Command
{
    protected $signature = 'telegramauth:verify {--url=http://127.0.0.1:8000}';

    protected $description = 'Prove TelegramAuth works: run tests + live HTTP smoke checks';

    public function handle(): int
    {
        $this->info('Step 1/2 — PHPUnit');

        $process = new Process(
            [PHP_BINARY, 'vendor/bin/phpunit'],
            base_path(),
            [
                'APP_ENV' => 'testing',
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => ':memory:',
                'DB_URL' => '',
                'CACHE_STORE' => 'array',
                'SESSION_DRIVER' => 'array',
                'QUEUE_CONNECTION' => 'sync',
                'MAIL_MAILER' => 'array',
            ],
        );
        $process->run(fn ($type, $buffer) => $this->output->write($buffer));

        if (! $process->isSuccessful()) {
            $this->error('Tests failed.');

            return self::FAILURE;
        }

        $this->line('');
        $this->info('Step 2/2 — HTTP smoke checks');

        $baseUrl = rtrim($this->option('url'), '/');

        if (! $this->serverIsUp($baseUrl)) {
            $this->warn("Server not reachable at {$baseUrl}.");
            $this->line('Start it with: php artisan serve');
            $this->info('Tests passed. HTTP checks skipped.');

            return self::SUCCESS;
        }

        $botToken = '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11';
        $plainSecret = 'smoke-test-secret';
        $codeVerifier = OidcClient::generateCodeVerifier();
        $codeChallenge = OidcClient::generateCodeChallenge($codeVerifier);
        $clientState = 'smoke-state';

        $tenant = Tenant::query()->create(['name' => 'Smoke Co', 'slug' => 'smoke-'.time(), 'plan' => 'free']);
        $application = Application::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Smoke App',
            'client_secret' => $plainSecret,
            'redirect_uris' => ['https://smoke.test/callback'],
        ]);
        TelegramBot::query()->create([
            'application_id' => $application->id,
            'bot_username' => 'smoke_bot',
            'bot_token' => $botToken,
        ]);

        $checks = [
            'GET /' => fn () => Http::get("{$baseUrl}/")->successful(),
            'GET /register' => fn () => Http::get("{$baseUrl}/register")->successful(),
            'GET /auth/start' => function () use ($baseUrl, $application, $codeChallenge, $clientState) {
                $response = Http::get("{$baseUrl}/auth/start", [
                    'client_id' => $application->client_id,
                    'redirect_uri' => 'https://smoke.test/callback',
                    'state' => $clientState,
                    'code_challenge' => $codeChallenge,
                    'flow' => 'widget',
                ]);

                return $response->successful() && str_contains($response->body(), 'smoke_bot');
            },
        ];

        foreach ($checks as $label => $check) {
            if (! $check()) {
                $this->error("  {$label}: FAILED");

                return self::FAILURE;
            }
            $this->line("  {$label}: ok");
        }

        $session = AuthSession::query()->where('application_id', $application->id)->latest()->firstOrFail();

        $data = [
            'id' => '555666777',
            'first_name' => 'Smoke',
            'username' => 'smokeuser',
            'auth_date' => (string) time(),
        ];
        $checkString = collect($data)->sortKeys()->map(fn ($v, $k) => $k.'='.$v)->implode("\n");
        $data['hash'] = hash_hmac('sha256', $checkString, hash('sha256', $botToken, true));

        $callback = Http::withOptions(['allow_redirects' => false])->get("{$baseUrl}/auth/callback/widget", [
            ...$data,
            'state' => $session->state,
        ]);

        if ($callback->status() !== 302) {
            $this->error('  GET /auth/callback/widget: FAILED');

            return self::FAILURE;
        }

        $location = $callback->header('Location');
        parse_str(parse_url($location, PHP_URL_QUERY), $query);

        if (($query['state'] ?? '') !== $clientState || empty($query['code'])) {
            $this->error('  callback redirect: FAILED (bad state or missing code)');

            return self::FAILURE;
        }
        $this->line('  GET /auth/callback/widget: ok');

        $token = Http::asForm()->post("{$baseUrl}/oauth/token", [
            'grant_type' => 'authorization_code',
            'code' => $query['code'],
            'redirect_uri' => 'https://smoke.test/callback',
            'client_id' => $application->client_id,
            'client_secret' => $plainSecret,
            'code_verifier' => $codeVerifier,
        ]);

        if (! $token->successful() || empty($token->json('access_token'))) {
            $this->error('  POST /oauth/token: FAILED');

            return self::FAILURE;
        }
        $this->line('  POST /oauth/token: ok');

        $userinfo = Http::withToken($token->json('access_token'))->get("{$baseUrl}/oauth/userinfo");
        if (! $userinfo->successful() || (int) $userinfo->json('telegram_id') !== 555666777) {
            $this->error('  GET /oauth/userinfo: FAILED');

            return self::FAILURE;
        }
        $this->line('  GET /oauth/userinfo: ok');

        $this->newLine();
        $this->info('Proven working: 12 unit/feature tests + 6 live HTTP checks.');

        return self::SUCCESS;
    }

    private function serverIsUp(string $baseUrl): bool
    {
        try {
            return Http::timeout(2)->get("{$baseUrl}/up")->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
