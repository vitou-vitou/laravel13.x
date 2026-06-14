<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ops-page-title">Integrations</h2>
            <p class="ops-page-subtitle">Outbound webhooks for n8n, Zapier, and automation</p>
        </div>
    </x-slot>

    <div class="ops-page">
        <div class="ops-container-narrow ops-stack">
            <x-flash />

            <p class="text-sm text-stone-600 leading-relaxed">
                Events fire when a creator approves or an operator publishes. Review governance in repo docs before pointing at production URLs.
            </p>

            <x-ops-panel title="Add webhook">
                <form method="POST" action="{{ route('operator.integrations.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="url" value="Webhook URL" />
                        <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" required placeholder="https://hooks.example.com/..." />
                    </div>
                    <div>
                        <x-input-label for="secret" value="Signing secret (optional)" />
                        <x-text-input id="secret" name="secret" class="mt-1 block w-full" placeholder="whsec_..." />
                    </div>
                    <div class="flex flex-wrap gap-6 text-sm">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="on_approved" value="1" checked class="rounded border-stone-300 text-indigo-600 focus:ring-indigo-500">
                            On approved
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="on_published" value="1" checked class="rounded border-stone-300 text-indigo-600 focus:ring-indigo-500">
                            On published
                        </label>
                    </div>
                    <x-primary-button>Add webhook</x-primary-button>
                </form>
            </x-ops-panel>

            <x-ops-panel title="Configured webhooks">
                <table class="ops-table">
                    <thead>
                        <tr>
                            <th>URL</th>
                            <th>Events</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($webhooks as $hook)
                            <tr>
                                <td class="truncate max-w-xs font-mono text-xs">{{ $hook->url }}</td>
                                <td class="space-x-1">
                                    @if ($hook->on_approved) <span class="ops-tag">approved</span> @endif
                                    @if ($hook->on_published) <span class="ops-tag">published</span> @endif
                                </td>
                                <td class="text-right space-x-3">
                                    <form method="POST" action="{{ route('operator.integrations.test', $hook) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="ops-link">Test</button>
                                    </form>
                                    <form method="POST" action="{{ route('operator.integrations.destroy', $hook) }}" class="inline" onsubmit="return confirm('Remove webhook?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ops-link-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <x-empty-state title="No webhooks configured">
                                        Add a URL above to receive approve and publish events.
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-ops-panel>
        </div>
    </div>
</x-app-layout>
