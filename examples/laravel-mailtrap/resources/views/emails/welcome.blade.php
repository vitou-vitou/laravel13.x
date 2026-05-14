<x-mail::message>
# Welcome, {{ $userName }}!

Thanks for joining **{{ config('app.name') }}**.

<x-mail::button :url="config('app.url')">
Visit Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
