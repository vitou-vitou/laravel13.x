<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\IntegrationWebhook;
use App\Services\IntegrationWebhookDispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IntegrationWebhookController extends Controller
{
    public function __construct(
        protected IntegrationWebhookDispatcher $dispatcher,
    ) {}

    public function index(Request $request): View
    {
        $webhooks = $request->user()
            ->integrationWebhooks()
            ->latest()
            ->get();

        return view('operator.integrations.index', compact('webhooks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'secret' => ['nullable', 'string', 'max:255'],
            'on_approved' => ['sometimes', 'boolean'],
            'on_published' => ['sometimes', 'boolean'],
        ]);

        $request->user()->integrationWebhooks()->create([
            'url' => $validated['url'],
            'secret' => $validated['secret'] ?? null,
            'on_approved' => $request->boolean('on_approved', true),
            'on_published' => $request->boolean('on_published', true),
            'is_active' => true,
        ]);

        return back()->with('status', 'Webhook added.');
    }

    public function destroy(Request $request, IntegrationWebhook $webhook): RedirectResponse
    {
        abort_unless($webhook->user_id === $request->user()->id, 403);

        $webhook->delete();

        return back()->with('status', 'Webhook removed.');
    }

    public function test(Request $request, IntegrationWebhook $webhook): RedirectResponse
    {
        abort_unless($webhook->user_id === $request->user()->id, 403);

        $delivery = $this->dispatcher->sendTestPing($webhook);

        return back()->with(
            'status',
            'Test ping sent — HTTP '.$delivery->response_status
        );
    }
}
