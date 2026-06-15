@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'ops-flash-success mb-4']) }} role="status">
        {{ $status }}
    </div>
@endif
