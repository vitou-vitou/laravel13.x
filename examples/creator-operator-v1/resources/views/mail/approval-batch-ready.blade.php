<x-mail::message>
# Approval batch ready

Hi {{ $creator->user?->name ?? '@'.$creator->handle }},

Your operator packaged **{{ $pendingCount }}** {{ \Illuminate\Support\Str::plural('video', $pendingCount) }} for review. Please approve or skip each within 24–48 hours.

<x-mail::button :url="route('creator.approvals.index')">
Open approval inbox
</x-mail::button>

Each card shows the proposed title, TikTok source, and packaging notes — nothing publishes until you approve.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
