<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Integrations</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            <p class="text-sm text-stone-600">Outbound webhooks for n8n / Zapier. Events fire on creator approve and operator publish. See governance in repo docs before pointing at production URLs.</p>

            <form method="POST" action="{{ route('operator.integrations.store') }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4 border border-stone-100">
                @csrf
                <div>
                    <x-input-label for="url" value="Webhook URL" />
                    <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" required />
                </div>
                <div>
                    <x-input-label for="secret" value="Signing secret (optional)" />
                    <x-text-input id="secret" name="secret" class="mt-1 block w-full" />
                </div>
                <div class="flex gap-6 text-sm">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="on_approved" value="1" checked class="rounded border-gray-300">
                        On approved
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="on_published" value="1" checked class="rounded border-gray-300">
                        On published
                    </label>
                </div>
                <x-primary-button>Add webhook</x-primary-button>
            </form>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-stone-100">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-2 text-left">URL</th>
                            <th class="px-4 py-2 text-left">Events</th>
                            <th class="px-4 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($webhooks as $hook)
                            <tr>
                                <td class="px-4 py-2 truncate max-w-xs">{{ $hook->url }}</td>
                                <td class="px-4 py-2">
                                    @if ($hook->on_approved) <span class="text-xs bg-stone-100 px-2 py-0.5 rounded">approved</span> @endif
                                    @if ($hook->on_published) <span class="text-xs bg-stone-100 px-2 py-0.5 rounded">published</span> @endif
                                </td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <form method="POST" action="{{ route('operator.integrations.test', $hook) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-indigo-600 hover:underline">Test</button>
                                    </form>
                                    <form method="POST" action="{{ route('operator.integrations.destroy', $hook) }}" class="inline" onsubmit="return confirm('Remove webhook?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-stone-500">No webhooks configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
