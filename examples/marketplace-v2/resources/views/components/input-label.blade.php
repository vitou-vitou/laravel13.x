@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-stone-700']) }}>
    {{ $value ?? $slot }}
</label>
