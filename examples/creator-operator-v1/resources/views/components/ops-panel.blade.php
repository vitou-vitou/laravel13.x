@props(['title' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'ops-panel']) }}>
    @if ($title)
        <div class="ops-panel-header">{{ $title }}</div>
    @endif

    <div class="ops-panel-body">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="ops-panel-footer">{{ $footer }}</div>
    @endif
</div>
