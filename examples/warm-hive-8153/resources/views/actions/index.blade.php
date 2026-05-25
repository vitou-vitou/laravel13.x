<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $project->icon }} {{ $project->name }}</h2>
    </x-slot>

    <div class="py-6" x-data="{ filtersOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Project tabs --}}
            <div class="flex gap-2 mb-5 overflow-x-auto">
                @foreach ($projects as $p)
                    <a href="/?project={{ $p->id }}"
                       class="flex-shrink-0 flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium transition {{ $p->id == $project->id ? $p->color . ' text-white' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm' }}">
                        {{ $p->icon }} {{ $p->name }}
                    </a>
                @endforeach
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
                @foreach ([['To Do',$stats['to_do'],'bg-gray-50 text-gray-700','border-gray-200'],['In Progress',$stats['in_progress'],'bg-blue-50 text-blue-700','border-blue-200'],['In Review',$stats['in_review'],'bg-yellow-50 text-yellow-700','border-yellow-200'],['Completed',$stats['completed'],'bg-green-50 text-green-700','border-green-200']] as [$l,$v,$tc,$bc])
                <div class="border {{ $bc }} rounded-lg p-3 text-center">
                    <div class="text-xs text-gray-500">{{ $l }}</div>
                    <div class="text-2xl font-bold {{ $tc }}">{{ $v }}</div>
                </div>
                @endforeach
                <div class="border border-amber-200 rounded-lg p-3 text-center bg-amber-50">
                    <div class="text-xs text-gray-500">Time Logged</div>
                    <div class="text-xl font-bold text-amber-700">{{ round($stats['total_time']/60,1) }}h</div>
                </div>
            </div>

            {{-- Analytics: by assignee --}}
            <div class="mb-5 bg-white rounded-lg shadow p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Team Overview</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase">
                                <th class="text-left pb-2 pr-6 font-medium">Member</th>
                                <th class="text-left pb-2 pr-6 font-medium">Total</th>
                                <th class="text-left pb-2 pr-6 font-medium">Completed</th>
                                <th class="text-left pb-2 pr-6 font-medium">Completion</th>
                                <th class="text-left pb-2 font-medium">Time Logged</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($byAssignee as $row)
                            @php $pct = $row['total'] > 0 ? round($row['done']/$row['total']*100) : 0; @endphp
                            <tr>
                                <td class="py-2 pr-6 font-medium text-gray-800 whitespace-nowrap">
                                    <span class="flex items-center gap-2">
                                        <span class="w-6 h-6 bg-amber-500 text-white rounded-full text-xs flex items-center justify-center font-medium">{{ substr($row['name'],0,1) }}</span>
                                        {{ $row['name'] }}
                                    </span>
                                </td>
                                <td class="py-2 pr-6 text-gray-600">{{ $row['total'] }}</td>
                                <td class="py-2 pr-6 text-gray-600">{{ $row['done'] }}</td>
                                <td class="py-2 pr-6">
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-green-500 h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="py-2 text-gray-600">{{ round($row['time']/60,1) }}h</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:hidden mb-3">
                <button @click="filtersOpen = !filtersOpen" class="flex items-center gap-2 text-sm font-medium text-amber-600 border border-amber-300 rounded-md px-3 py-2 hover:bg-amber-50">
                    <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-5">
                <aside class="w-full lg:w-48 lg:flex-shrink-0 lg:!block" x-show="filtersOpen" x-transition>
                    <div class="bg-white rounded-lg shadow p-4">
                        <form method="GET" action="/">
                            <input type="hidden" name="project" value="{{ $project->id }}">
                            @foreach ([['status','Status',['to_do'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','completed'=>'Completed']],['priority','Priority',['critical'=>'Critical','high'=>'High','medium'=>'Medium','low'=>'Low']]] as [$field,$label,$opts])
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                                <select name="{{ $field }}" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach ($opts as $v=>$l)
                                        <option value="{{ $v }}" @selected(request($field)===$v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endforeach
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Label</label>
                                <select name="label" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach ($labels as $l)
                                        <option value="{{ $l }}" @selected(request('label')===$l)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs text-gray-500 mb-1">Assignee</label>
                                <select name="assignee" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach ($assignees as $a)
                                        <option value="{{ $a }}" @selected(request('assignee')===$a)>{{ $a }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-amber-500 text-white text-xs py-2 rounded-md hover:bg-amber-600">Apply</button>
                                <a href="/?project={{ $project->id }}" class="flex-1 text-center bg-gray-100 text-gray-700 text-xs py-2 rounded-md">Reset</a>
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
                                        @foreach (['Action','Status','Priority','Assignee','Label','Due','Time'] as $h)
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $h }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($actions as $action)
                                    @php
                                        $sc = ['to_do'=>'bg-gray-100 text-gray-600','in_progress'=>'bg-blue-100 text-blue-700','in_review'=>'bg-yellow-100 text-yellow-700','completed'=>'bg-green-100 text-green-700'][$action->status];
                                        $sl = ['to_do'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','completed'=>'Completed'][$action->status];
                                        $pc = ['critical'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','medium'=>'bg-yellow-100 text-yellow-700','low'=>'bg-gray-100 text-gray-500'][$action->priority];
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $action->title }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $sc }}">{{ $sl }}</span></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $pc }}">{{ ucfirst($action->priority) }}</span></td>
                                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $action->assignee ?? '—' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($action->label)<span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-xs">{{ $action->label }}</span>@else<span class="text-gray-400 text-xs">—</span>@endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $action->due_date ? \Carbon\Carbon::parse($action->due_date)->format('M d') : '—' }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $action->time_logged ? round($action->time_logged/60,1).'h' : '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No actions found.</td></tr>
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
