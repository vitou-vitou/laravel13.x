<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="text-xl">{{ $project->icon }}</span>
            <h2 class="font-semibold text-xl text-gray-800">{{ $project->name }}</h2>
            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-mono">{{ $project->key }}</span>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Project tabs --}}
            <div class="flex gap-2 mb-5 overflow-x-auto">
                @foreach ($projects as $p)
                    <a href="/?project={{ $p->id }}"
                       class="flex-shrink-0 flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium transition {{ $p->id == $project->id ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm' }}">
                        {{ $p->icon }} {{ $p->name }}
                    </a>
                @endforeach
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-4 gap-3 mb-5">
                @foreach ([['To Do',$stats['todo'],'bg-gray-50 border-gray-200','text-gray-700'],['In Progress',$stats['in_progress'],'bg-blue-50 border-blue-200','text-blue-700'],['In Review',$stats['in_review'],'bg-yellow-50 border-yellow-200','text-yellow-700'],['Done',$stats['done'],'bg-green-50 border-green-200','text-green-700']] as [$l,$v,$bg,$tc])
                <div class="border rounded-lg p-3 text-center {{ $bg }}">
                    <div class="text-xs text-gray-500">{{ $l }}</div>
                    <div class="text-2xl font-bold {{ $tc }}">{{ $v }}</div>
                </div>
                @endforeach
            </div>

            {{-- View tabs --}}
            <div class="flex gap-1 mb-4 border-b border-gray-200">
                @foreach (['board'=>'🗂 Board','backlog'=>'📋 Backlog'] as $v=>$label)
                <a href="/?project={{ $project->id }}&view={{ $v }}"
                   class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition {{ $view===$v ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-800' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>

            @if ($view === 'board')
                {{-- Active sprint info --}}
                @if ($activeSprint)
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="font-semibold text-gray-800">{{ $activeSprint->name }}</span>
                        <span class="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Active</span>
                        <span class="ml-2 text-xs text-gray-500">{{ \Carbon\Carbon::parse($activeSprint->start_date)->format('M d') }} – {{ \Carbon\Carbon::parse($activeSprint->end_date)->format('M d') }}</span>
                    </div>
                    <form method="GET" action="/" class="flex gap-2">
                        <input type="hidden" name="project" value="{{ $project->id }}">
                        <input type="hidden" name="view" value="board">
                        <select name="type" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm py-1 px-2">
                            <option value="">All Types</option>
                            @foreach (['story','bug','task','epic'] as $t)
                                <option value="{{ $t }}" @selected(request('type')===$t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                        <select name="assignee" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm py-1 px-2">
                            <option value="">All Assignees</option>
                            @foreach ($assignees as $a)
                                <option value="{{ $a }}" @selected(request('assignee')===$a)>{{ $a }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Kanban board --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach (['todo'=>'To Do','in_progress'=>'In Progress','in_review'=>'In Review','done'=>'Done'] as $status=>$label)
                    @php $cols = $boardIssues->where('status',$status); @endphp
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-gray-600 uppercase">{{ $label }}</span>
                            <span class="text-xs bg-gray-200 text-gray-600 rounded-full px-2">{{ $cols->count() }}</span>
                        </div>
                        <div class="space-y-2">
                            @forelse ($cols as $issue)
                            @php
                                $tc = ['story'=>'bg-green-100 text-green-700','bug'=>'bg-red-100 text-red-700','task'=>'bg-blue-100 text-blue-700','epic'=>'bg-purple-100 text-purple-700'][$issue->type];
                                $pc = ['lowest'=>'↓','low'=>'↓','medium'=>'→','high'=>'↑','highest'=>'⬆'][$issue->priority];
                                $pcol = ['lowest'=>'text-gray-400','low'=>'text-blue-400','medium'=>'text-yellow-500','high'=>'text-orange-500','highest'=>'text-red-600'][$issue->priority];
                            @endphp
                            <div class="bg-white rounded-md p-3 shadow-sm border border-gray-200">
                                <div class="flex items-start justify-between gap-1 mb-1">
                                    <span class="text-xs px-1.5 py-0.5 rounded font-medium {{ $tc }}">{{ ucfirst($issue->type) }}</span>
                                    <span class="{{ $pcol }} font-bold text-sm">{{ $pc }}</span>
                                </div>
                                <p class="text-xs font-medium text-gray-800 leading-snug mb-2">{{ $issue->title }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400 font-mono">{{ $issue->key }}</span>
                                    <div class="flex items-center gap-1">
                                        @if($issue->story_points)
                                            <span class="text-xs bg-gray-100 text-gray-500 rounded px-1">{{ $issue->story_points }}sp</span>
                                        @endif
                                        @if($issue->assignee)
                                            <span class="w-5 h-5 bg-indigo-500 text-white rounded-full text-xs flex items-center justify-center font-medium">{{ substr($issue->assignee,0,1) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p class="text-xs text-gray-400 text-center py-4">No issues</p>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500">No active sprint.</p>
                @endif

            @else
                {{-- Backlog --}}
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <span class="font-medium text-sm text-gray-700">Backlog ({{ $backlog->count() }} issues)</span>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach (['Key','Title','Type','Priority','Assignee','Points'] as $h)
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($backlog as $issue)
                            @php
                                $tc = ['story'=>'bg-green-100 text-green-700','bug'=>'bg-red-100 text-red-700','task'=>'bg-blue-100 text-blue-700','epic'=>'bg-purple-100 text-purple-700'][$issue->type];
                                $pc = ['lowest'=>'↓↓','low'=>'↓','medium'=>'→','high'=>'↑','highest'=>'⬆↑'][$issue->priority];
                                $pcol = ['lowest'=>'text-gray-400','low'=>'text-blue-500','medium'=>'text-yellow-600','high'=>'text-orange-500','highest'=>'text-red-600'][$issue->priority];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-mono text-xs text-gray-500 whitespace-nowrap">{{ $issue->key }}</td>
                                <td class="px-4 py-2.5 text-gray-800 font-medium">{{ $issue->title }}</td>
                                <td class="px-4 py-2.5 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $tc }}">{{ ucfirst($issue->type) }}</span></td>
                                <td class="px-4 py-2.5 whitespace-nowrap"><span class="font-bold {{ $pcol }}">{{ $pc }}</span> <span class="text-xs text-gray-500">{{ ucfirst($issue->priority) }}</span></td>
                                <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap">{{ $issue->assignee ?? '—' }}</td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    @if($issue->story_points)
                                    <span class="bg-gray-100 text-gray-600 rounded px-2 py-0.5 text-xs">{{ $issue->story_points }}sp</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Backlog is empty.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Past sprints --}}
                @php $completed = $sprints->where('status','completed'); @endphp
                @if($completed->count())
                <div class="mt-5">
                    <h3 class="text-sm font-semibold text-gray-600 mb-3">Completed Sprints</h3>
                    @foreach ($completed as $sprint)
                    @php $sIssues = DB::table('jira_issues')->where('sprint_id',$sprint->id)->get(); @endphp
                    <div class="bg-white rounded-lg shadow mb-3 overflow-hidden">
                        <div class="px-4 py-2 bg-gray-50 border-b flex items-center gap-2">
                            <span class="font-medium text-sm text-gray-700">{{ $sprint->name }}</span>
                            <span class="text-xs bg-gray-200 text-gray-500 px-2 py-0.5 rounded-full">Completed</span>
                            <span class="text-xs text-gray-400 ml-auto">{{ $sIssues->count() }} issues</span>
                        </div>
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <tbody>
                                @foreach ($sIssues as $issue)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-mono text-xs text-gray-400 whitespace-nowrap w-24">{{ $issue->key }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $issue->title }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        @php $tc2 = ['story'=>'bg-green-100 text-green-700','bug'=>'bg-red-100 text-red-700','task'=>'bg-blue-100 text-blue-700','epic'=>'bg-purple-100 text-purple-700'][$issue->type]; @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $tc2 }}">{{ ucfirst($issue->type) }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-500 whitespace-nowrap">{{ $issue->assignee }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap"><span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded">Done</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endforeach
                </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
