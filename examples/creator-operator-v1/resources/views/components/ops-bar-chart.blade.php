@props([
    'title',
    'series' => [],
    'accent' => 'indigo',
    'empty' => 'No data yet',
])

@php
    $max = max(1, collect($series)->max('value') ?? 0);
    $accents = [
        'indigo' => 'bg-indigo-500',
        'amber' => 'bg-amber-500',
        'emerald' => 'bg-emerald-500',
    ];
    $barClass = $accents[$accent] ?? $accents['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'ops-chart']) }}>
    @if ($title !== '')
        <div class="ops-chart-title">{{ $title }}</div>
    @endif
    @if (count($series) === 0 || collect($series)->sum('value') === 0)
        <p class="ops-chart-empty">{{ $empty }}</p>
    @else
        <div class="ops-chart-bars" role="img" aria-label="{{ $title }}">
            @foreach ($series as $point)
                @php
                    $height = max(4, (int) round(($point['value'] / $max) * 100));
                @endphp
                <div class="ops-chart-bar-col">
                    <div class="ops-chart-bar-value tabular-nums">{{ $point['value'] }}</div>
                    <div class="ops-chart-bar-track">
                        <div
                            class="ops-chart-bar-fill {{ $barClass }}"
                            style="height: {{ $height }}%"
                            title="{{ ($point['date'] ?? $point['label']).': '.$point['value'] }}"
                        ></div>
                    </div>
                    <div class="ops-chart-bar-label">{{ $point['label'] }}</div>
                </div>
            @endforeach
        </div>
    @endif
</div>
