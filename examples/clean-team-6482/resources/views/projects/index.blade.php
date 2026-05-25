<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="text-xl">{{ $project->icon }}</span>
            <h2 class="font-semibold text-xl text-gray-800">{{ $project->name }}</h2>
            @if($project->company)
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">{{ $project->company }}</span>
            @endif
            @php $psc = ['active'=>'bg-green-100 text-green-700','on_hold'=>'bg-yellow-100 text-yellow-700','completed'=>'bg-gray-100 text-gray-500'][$project->status]; @endphp
            <span class="text-xs {{ $psc }} px-2 py-0.5 rounded-full font-medium">{{ ucfirst(str_replace('_',' ',$project->status)) }}</span>
        </div>
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

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                @foreach ([['New',$stats['new'],'bg-gray-50 text-gray-700','border-gray-200'],['In Progress',$stats['in_progress'],'bg-blue-50 text-blue-700','border-blue-200'],['Completed',$stats['completed'],'bg-green-50 text-green-700','border-green-200'],['Milestones',$stats['milestones'],'bg-purple-50 text-purple-700','border-purple-200']] as [$l,$v,$tc,$bc])
                <div class="border {{ $bc }} rounded-lg p-3 text-center">
                    <div class="text-xs text-gray-500">{{ $l }}</div>
                    <div class="text-2xl font-bold {{ $tc }}">{{ $v }}</div>
                </div>
                @endforeach
            </div>

            {{-- Milestones --}}
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-gray-600 mb-2">Milestones</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($milestones as $ms)
                    @php
                        $mc = $ms->completed ? 'bg-green-100 text-green-700 border-green-200' : 'bg-white text-gray-700 border-gray-200';
                        $msTaskCount = $tasksByMilestone->get($ms->id, collect())->count();
                        $msDone = $tasksByMilestone->get($ms->id, collect())->where('status','completed')->count();
                    @endphp
                    <div class="flex items-center gap-2 px-3 py-2 border rounded-lg {{ $mc }}">
                        <span class="text-sm">{{ $ms->completed ? '✅' : '🎯' }}</span>
                        <div>
                            <div class="text-xs font-medium">{{ $ms->title }}</div>
                            <div class="text-xs text-gray-500">Due {{ \Carbon\Carbon::parse($ms->due_date)->format('M d') }} · {{ $msDone }}/{{ $msTaskCount }} tasks</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="lg:hidden mb-3">
                <button @click="filtersOpen = !filtersOpen" class="flex items-center gap-2 text-sm font-medium text-teal-600 border border-teal-300 rounded-md px-3 py-2 hover:bg-teal-50">
                    <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-5">
                <aside class="w-full lg:w-48 lg:flex-shrink-0 lg:!block" x-show="filtersOpen" x-transition>
                    <div class="bg-white rounded-lg shadow p-4">
                        <form method="GET" action="/">
                            <input type="hidden" name="project" value="{{ $project->id }}">
                            @foreach ([['status','Status',['new'=>'New','in_progress'=>'In Progress','completed'=>'Completed']],['priority','Priority',['high'=>'High','medium'=>'Medium','low'=>'Low']]] as [$field,$label,$opts])
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
                                <label class="block text-xs text-gray-500 mb-1">Milestone</label>
                                <select name="milestone" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach ($milestones as $ms)
                                        <option value="{{ $ms->id }}" @selected(request('milestone')==$ms->id)>{{ $ms->title }}</option>
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
                                <button type="submit" class="flex-1 bg-teal-600 text-white text-xs py-2 rounded-md hover:bg-teal-700">Apply</button>
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
                                        @foreach (['Task','Status','Priority','Assignee','Milestone','Due','Est.'] as $h)
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $h }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($tasks as $task)
                                    @php
                                        $sc = ['new'=>'bg-gray-100 text-gray-600','in_progress'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700'][$task->status];
                                        $pc = ['high'=>'bg-red-100 text-red-700','medium'=>'bg-yellow-100 text-yellow-700','low'=>'bg-gray-100 text-gray-500'][$task->priority];
                                        $ms = $milestones->firstWhere('id', $task->milestone_id);
                                    @endphp
                                    <tr class="hover:bg-gray-50 {{ $task->status === 'completed' ? 'opacity-60' : '' }}">
                                        <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap {{ $task->status === 'completed' ? 'line-through' : '' }}">{{ $task->title }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $sc }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $pc }}">{{ ucfirst($task->priority) }}</span></td>
                                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $task->assignee ?? '—' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($ms)<span class="text-xs text-purple-700 bg-purple-50 px-2 py-0.5 rounded truncate max-w-32 block">{{ $ms->title }}</span>@else<span class="text-gray-400 text-xs">—</span>@endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d') : '—' }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $task->estimated_minutes ? round($task->estimated_minutes/60,1).'h' : '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No tasks found.</td></tr>
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
