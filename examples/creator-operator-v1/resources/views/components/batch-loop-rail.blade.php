@props(['current' => null])

@php
    $steps = [
        1 => ['label' => 'Build list', 'hint' => 'New TikTok URLs'],
        2 => ['label' => 'Eligibility', 'hint' => 'Music & brand'],
        3 => ['label' => 'Asset', 'hint' => 'Clean file'],
        4 => ['label' => 'Packaging', 'hint' => 'Titles & notes'],
        5 => ['label' => 'Approval', 'hint' => 'Creator 24–48h'],
        6 => ['label' => 'Publish', 'hint' => 'URLs & log'],
        7 => ['label' => 'Report', 'hint' => 'Metrics & settlement'],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'ops-panel ops-panel-body']) }}>
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
        <div>
            <p class="text-sm font-semibold text-stone-900">Weekly batch loop</p>
            <p class="text-xs text-stone-500 mt-0.5">Seven steps from BUILD LIST through REPORT</p>
        </div>
        <p class="text-[10px] font-medium uppercase tracking-wider text-stone-400">Checklist aligned</p>
    </div>
    <ol class="grid gap-2 sm:grid-cols-7">
        @foreach ($steps as $num => $step)
            @php
                $active = (int) $current === $num;
                $future = $current !== null && (int) $current < $num;
                $done = ! $active && ! $future && $current !== null && (int) $current > $num;
            @endphp
            <li @class([
                'rounded-lg border px-2 py-2.5 text-center transition-colors',
                'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-600/20 shadow-sm' => $active,
                'border-stone-200 bg-stone-50/80 text-stone-400' => $future && ! $active,
                'border-emerald-200 bg-emerald-50/60' => $done,
                'border-stone-200 bg-white' => $current === null && ! $active,
                'border-stone-200 bg-white' => ! $active && ! $future && ! $done && $current !== null,
            ])>
                <div @class([
                    'text-[11px] font-semibold leading-tight',
                    'text-indigo-800' => $active,
                    'text-emerald-800' => $done,
                    'text-stone-700' => ! $active && ! $future && ! $done,
                    'text-stone-400' => $future,
                ])>{{ $num }}. {{ $step['label'] }}</div>
                <div class="text-[10px] leading-tight text-stone-500 mt-1 hidden sm:block">{{ $step['hint'] }}</div>
            </li>
        @endforeach
    </ol>
</div>
