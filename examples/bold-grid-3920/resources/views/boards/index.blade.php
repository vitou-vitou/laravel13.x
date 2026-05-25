<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $board->icon }} {{ $board->name }}</h2>
    </x-slot>

    <div class="py-6" x-data="{ filtersOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Board tabs --}}
            <div class="flex gap-2 mb-5 overflow-x-auto">
                @foreach ($boards as $b)
                    <a href="/?board={{ $b->id }}"
                       class="flex-shrink-0 flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium transition {{ $b->id == $board->id ? $b->color . ' text-white' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm' }}">
                        {{ $b->icon }} {{ $b->name }}
                    </a>
                @endforeach
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                @foreach ([['Done',$stats['done'],'bg-green-50 text-green-700','border-green-200'],['Working',$stats['working_on_it'],'bg-yellow-50 text-yellow-700','border-yellow-200'],['Stuck',$stats['stuck'],'bg-red-50 text-red-700','border-red-200'],['Not Started',$stats['not_started'],'bg-gray-50 text-gray-600','border-gray-200']] as [$l,$v,$tc,$bc])
                <div class="border {{ $bc }} rounded-lg p-3 text-center">
                    <div class="text-xs text-gray-500">{{ $l }}</div>
                    <div class="text-2xl font-bold {{ $tc }}">{{ $v }}</div>
                </div>
                @endforeach
            </div>

            {{-- Filters --}}
            <div class="flex flex-wrap gap-2 mb-4 items-center">
                <form method="GET" action="/" class="flex flex-wrap gap-2">
                    <input type="hidden" name="board" value="{{ $board->id }}">
                    <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm py-1.5 px-3">
                        <option value="">All Status</option>
                        @foreach (['working_on_it'=>'Working on it','done'=>'Done','stuck'=>'Stuck','not_started'=>'Not started'] as $v=>$l)
                            <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                        @endforeach
                    </select>
                    <select name="assignee" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm py-1.5 px-3">
                        <option value="">All Assignees</option>
                        @foreach ($assignees as $a)
                            <option value="{{ $a }}" @selected(request('assignee')===$a)>{{ $a }}</option>
                        @endforeach
                    </select>
                    @if(request()->hasAny(['status','assignee']))
                        <a href="/?board={{ $board->id }}" class="text-xs text-gray-500 hover:text-gray-700 self-center">Clear</a>
                    @endif
                </form>
            </div>

            {{-- Groups --}}
            <div class="space-y-5">
                @foreach ($groups as $group)
                @php $items = $allItems->get($group->id, collect()); @endphp
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
                        <div class="w-3 h-3 rounded-sm {{ $group->color }}"></div>
                        <span class="font-semibold text-sm text-gray-800">{{ $group->name }}</span>
                        <span class="text-xs text-gray-400">{{ $items->count() }} items</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-100">
                                <tr>
                                    @foreach (['Item','Status','Assignee','Due Date','Priority','Progress'] as $h)
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $h }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($items as $item)
                                @php
                                    $sc = ['working_on_it'=>'bg-yellow-100 text-yellow-700','done'=>'bg-green-100 text-green-700','stuck'=>'bg-red-100 text-red-700','not_started'=>'bg-gray-100 text-gray-600'][$item->status];
                                    $sl = ['working_on_it'=>'Working on it','done'=>'Done','stuck'=>'Stuck','not_started'=>'Not started'][$item->status];
                                    $pc = ['critical'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','medium'=>'bg-yellow-100 text-yellow-700','low'=>'bg-gray-100 text-gray-500'][$item->priority];
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 font-medium text-gray-800 whitespace-nowrap">{{ $item->title }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-semibold {{ $sc }}">{{ $sl }}</span></td>
                                    <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">
                                        @if($item->assignee)
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-6 h-6 bg-indigo-500 text-white rounded-full text-xs flex items-center justify-center font-medium">{{ substr($item->assignee,0,1) }}</span>
                                            {{ $item->assignee }}
                                        </span>
                                        @else —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('M d') : '—' }}</td>
                                    <td class="px-4 py-2.5 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $pc }}">{{ ucfirst($item->priority) }}</span></td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-indigo-500 h-1.5 rounded-full" style="width:{{ $item->progress }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-400">{{ $item->progress }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="px-4 py-4 text-center text-gray-400 text-sm">No items.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
