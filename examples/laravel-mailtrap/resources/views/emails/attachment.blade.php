<x-mail::message>
# Hello, {{ $userName }}!

Please find your requested document attached to this email.

<x-mail::button :url="config('app.url')">
Visit Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
