@props(['creator'])

<nav class="flex flex-wrap gap-2 text-sm border-b border-stone-200 pb-3">
    <a href="{{ route('operator.creators.show', $creator) }}"
       @class(['rounded-full px-3 py-1 font-medium border', 'bg-stone-900 text-white border-stone-900' => request()->routeIs('operator.creators.show'), 'bg-white text-stone-700 border-stone-300 hover:bg-stone-50' => ! request()->routeIs('operator.creators.show')])>
        Publish log
    </a>
    <a href="{{ route('operator.creators.metrics.index', $creator) }}"
       @class(['rounded-full px-3 py-1 font-medium border', 'bg-stone-900 text-white border-stone-900' => request()->routeIs('operator.creators.metrics.*'), 'bg-white text-stone-700 border-stone-300 hover:bg-stone-50' => ! request()->routeIs('operator.creators.metrics.*')])>
        Weekly metrics
    </a>
    <a href="{{ route('operator.creators.settlement.index', $creator) }}"
       @class(['rounded-full px-3 py-1 font-medium border', 'bg-stone-900 text-white border-stone-900' => request()->routeIs('operator.creators.settlement.*'), 'bg-white text-stone-700 border-stone-300 hover:bg-stone-50' => ! request()->routeIs('operator.creators.settlement.*')])>
        Settlement
    </a>
    <a href="{{ route('operator.creators.import.index', $creator) }}"
       @class(['rounded-full px-3 py-1 font-medium border', 'bg-stone-900 text-white border-stone-900' => request()->routeIs('operator.creators.import.*'), 'bg-white text-stone-700 border-stone-300 hover:bg-stone-50' => ! request()->routeIs('operator.creators.import.*')])>
        TikTok import
    </a>
</nav>
