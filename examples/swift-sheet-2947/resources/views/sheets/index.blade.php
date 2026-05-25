<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $sheet->icon }} {{ $sheet->name }}</h2>
    </x-slot>

    <div class="py-6" x-data="{ filtersOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Sheet tabs --}}
            <div class="flex gap-2 mb-5 overflow-x-auto">
                @foreach ($sheets as $s)
                    <a href="/?sheet={{ $s->id }}"
                       class="flex-shrink-0 flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium transition {{ $s->id == $sheet->id ? $s->color . ' text-white' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm' }}">
                        {{ $s->icon }} {{ $s->name }}
                    </a>
                @endforeach
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
                @foreach ([
                    ['Total',$stats['total'],'bg-gray-50 text-gray-700','border-gray-200'],
                    ['Complete',$stats['complete'],'bg-green-50 text-green-700','border-green-200'],
                    ['In Progress',$stats['in_progress'],'bg-blue-50 text-blue-700','border-blue-200'],
                    ['Not Started',$stats['not_started'],'bg-gray-50 text-gray-500','border-gray-200'],
                ] as [$l,$v,$tc,$bc])
                <div class="border {{ $bc }} rounded-lg p-3 text-center">
                    <div class="text-xs text-gray-500">{{ $l }}</div>
                    <div class="text-xl font-bold {{ $tc }}">{{ $v }}</div>
                </div>
                @endforeach
                @if($stats['budget'])
                <div class="border border-emerald-200 rounded-lg p-3 text-center bg-emerald-50">
                    <div class="text-xs text-gray-500">Budget</div>
                    <div class="text-base font-bold text-emerald-700">${{ number_format($stats['budget']) }}</div>
                </div>
                <div class="border border-orange-200 rounded-lg p-3 text-center bg-orange-50">
                    <div class="text-xs text-gray-500">Actual Cost</div>
                    <div class="text-base font-bold text-orange-700">${{ number_format($stats['actual_cost']) }}</div>
                </div>
                @endif
            </div>

            <div class="lg:hidden mb-3">
                <button @click="filtersOpen = !filtersOpen" class="flex items-center gap-2 text-sm font-medium text-emerald-600 border border-emerald-300 rounded-md px-3 py-2 hover:bg-emerald-50">
                    <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-5">
                <aside class="w-full lg:w-48 lg:flex-shrink-0 lg:!block" x-show="filtersOpen" x-transition>
                    <div class="bg-white rounded-lg shadow p-4">
                        <form method="GET" action="/">
                            <input type="hidden" name="sheet" value="{{ $sheet->id }}">
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Status</label>
                                <select name="status" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach (['not_started'=>'Not Started','in_progress'=>'In Progress','complete'=>'Complete','on_hold'=>'On Hold','blocked'=>'Blocked'] as $v=>$l)
                                        <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Priority</label>
                                <select name="priority" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach (['critical','high','medium','low','none'] as $o)
                                        <option value="{{ $o }}" @selected(request('priority')===$o)>{{ ucfirst($o) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs text-gray-500 mb-1">Assigned To</label>
                                <select name="assigned_to" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach ($assignees as $a)
                                        <option value="{{ $a }}" @selected(request('assigned_to')===$a)>{{ $a }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-emerald-600 text-white text-xs py-2 rounded-md hover:bg-emerald-700">Apply</button>
                                <a href="/?sheet={{ $sheet->id }}" class="flex-1 text-center bg-gray-100 text-gray-700 text-xs py-2 rounded-md">Reset</a>
                            </div>
                        </form>
                    </div>
                </aside>

                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @foreach (['#','Task Name','Assigned To','Status','Priority','Start','End','Duration','% Done','Budget','Actual'] as $h)
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $h }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($rows as $row)
                                    @php
                                        $sc = ['not_started'=>'bg-gray-100 text-gray-600','in_progress'=>'bg-blue-100 text-blue-700','complete'=>'bg-green-100 text-green-700','on_hold'=>'bg-yellow-100 text-yellow-700','blocked'=>'bg-red-100 text-red-700'][$row->status];
                                        $sl = ['not_started'=>'Not Started','in_progress'=>'In Progress','complete'=>'Complete','on_hold'=>'On Hold','blocked'=>'Blocked'][$row->status];
                                        $pc = ['critical'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','medium'=>'bg-yellow-100 text-yellow-700','low'=>'bg-blue-100 text-blue-600','none'=>''][$row->priority];
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2.5 text-gray-400 text-xs whitespace-nowrap">{{ $row->row_order }}</td>
                                        <td class="px-3 py-2.5 font-medium text-gray-800 whitespace-nowrap">{{ $row->task_name }}</td>
                                        <td class="px-3 py-2.5 text-gray-500 whitespace-nowrap">
                                            @if($row->assigned_to)
                                            <span class="flex items-center gap-1.5">
                                                <span class="w-5 h-5 bg-emerald-500 text-white rounded-full text-xs flex items-center justify-center font-medium">{{ substr($row->assigned_to,0,1) }}</span>
                                                {{ $row->assigned_to }}
                                            </span>
                                            @else —
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $sc }}">{{ $sl }}</span></td>
                                        <td class="px-3 py-2.5 whitespace-nowrap">
                                            @if($row->priority !== 'none')<span class="px-2 py-0.5 rounded text-xs font-medium {{ $pc }}">{{ ucfirst($row->priority) }}</span>@else<span class="text-gray-300 text-xs">—</span>@endif
                                        </td>
                                        <td class="px-3 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('M d') : '—' }}</td>
                                        <td class="px-3 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('M d') : '—' }}</td>
                                        <td class="px-3 py-2.5 text-xs text-gray-400 whitespace-nowrap">{{ $row->duration ? $row->duration.'d' : '—' }}</td>
                                        <td class="px-3 py-2.5 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-14 bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width:{{ $row->percent_complete }}%"></div>
                                                </div>
                                                <span class="text-xs text-gray-500">{{ $row->percent_complete }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2.5 text-xs text-gray-500 whitespace-nowrap">{{ $row->budget ? '$'.number_format($row->budget) : '—' }}</td>
                                        <td class="px-3 py-2.5 text-xs whitespace-nowrap {{ $row->actual_cost > $row->budget && $row->budget ? 'text-red-600 font-medium' : 'text-gray-500' }}">{{ $row->actual_cost ? '$'.number_format($row->actual_cost) : '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">No rows found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
