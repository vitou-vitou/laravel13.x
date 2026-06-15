@props(['creator'])

<nav class="flex flex-wrap gap-2 text-sm border-b border-stone-200 pb-4">
    <a href="{{ route('operator.creators.show', $creator) }}"
       @class(['ops-chip-active' => request()->routeIs('operator.creators.show'), 'ops-chip-inactive' => ! request()->routeIs('operator.creators.show')])>
        Publish log
    </a>
    <a href="{{ route('operator.creators.metrics.index', $creator) }}"
       @class(['ops-chip-active' => request()->routeIs('operator.creators.metrics.*'), 'ops-chip-inactive' => ! request()->routeIs('operator.creators.metrics.*')])>
        Weekly metrics
    </a>
    <a href="{{ route('operator.creators.settlement.index', $creator) }}"
       @class(['ops-chip-active' => request()->routeIs('operator.creators.settlement.*'), 'ops-chip-inactive' => ! request()->routeIs('operator.creators.settlement.*')])>
        Settlement
    </a>
    <a href="{{ route('operator.creators.import.index', $creator) }}"
       @class(['ops-chip-active' => request()->routeIs('operator.creators.import.*'), 'ops-chip-inactive' => ! request()->routeIs('operator.creators.import.*')])>
        TikTok import
    </a>
</nav>
