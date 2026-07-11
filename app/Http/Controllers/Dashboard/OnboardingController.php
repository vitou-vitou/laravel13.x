<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\TelegramBot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(): View
    {
        $tenant = auth()->user()->tenant()->with('applications.telegramBot')->first();

        return view('dashboard.onboarding', [
            'tenant' => $tenant,
            'application' => $tenant?->applications->first(),
        ]);
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'redirect_uri' => ['required', 'url'],
        ]);

        $tenant = auth()->user()->tenant;
        $plainSecret = \Illuminate\Support\Str::random(64);

        $application = Application::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'client_secret' => $plainSecret,
            'redirect_uris' => [$validated['redirect_uri']],
        ]);

        return redirect()->route('dashboard.onboarding')
            ->with('status', 'Application created.')
            ->with('client_secret', $plainSecret)
            ->with('client_id', $application->client_id);
    }

    public function storeBot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_id' => ['required', 'exists:applications,id'],
            'bot_username' => ['required', 'string', 'max:255'],
            'bot_token' => ['required', 'string'],
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $application = Application::query()
            ->where('id', $validated['application_id'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        TelegramBot::query()->updateOrCreate(
            ['application_id' => $application->id],
            [
                'bot_username' => ltrim($validated['bot_username'], '@'),
                'bot_token' => $validated['bot_token'],
                'domains' => [$validated['domain']],
            ]
        );

        return redirect()->route('dashboard.onboarding')->with('status', 'Telegram bot connected.');
    }
}
