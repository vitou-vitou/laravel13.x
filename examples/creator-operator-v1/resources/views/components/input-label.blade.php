@props(['value'])

<label {{ $attributes->merge(['class' => 'ops-label']) }}>
    {{ $value ?? $slot }}
</label>
