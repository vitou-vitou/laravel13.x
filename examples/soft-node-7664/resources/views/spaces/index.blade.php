<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $space->icon }} {{ $space->name }}</h2>
    </x-slot>

    <div class="py-6" x-data="{ filtersOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Space tabs --}}
            <div class="flex gap-2 mb-5 overflow-x-auto">
                @foreach ($spaces as $s)
                    <a href="/?space={{ $s->id }}"
                       class="flex-shrink-0 flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium transition {{ $s->id == $space->id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm' }}">
                        {{ $s->icon }} {{ $s->name }}
                    </a>
                @endforeach
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-5">
                @foreach ([['Open',$stats['open'],'bg-blue-50 text-blue-700'],['In Progress',$stats['in_progress'],'bg-yellow-50 text-yellow-700'],['Closed',$stats['closed'],'bg-green-50 text-green-700']] as [$label,$val,$cls])
                    <div class="bg-white rounded-lg shadow p-3 text-center">
                        <div class="text-xs text-gray-500">{{ $label }}</div>
                        <div class="text-xl font-bold {{ $cls }} rounded mt-1">{{ $val }}</div>
                    </div>
                @endforeach
            </div>

            <div class="lg:hidden mb-3">
                <button @click="filtersOpen = !filtersOpen" class="flex items-center gap-2 text-sm font-medium text-indigo-600 border border-indigo-300 rounded-md px-3 py-2 hover:bg-indigo-50">
                    <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-5">
                <aside class="w-full lg:w-48 lg:flex-shrink-0 lg:!block" x-show="filtersOpen" x-transition>
                    <div class="bg-white rounded-lg shadow p-4">
                        <form method="GET" action="/">
                            <input type="hidden" name="space" value="{{ $space->id }}">
                            @foreach ([['status','Status',['open','in_progress','in_review','closed']],['priority','Priority',['urgent','high','normal','low']]] as [$field,$label,$opts])
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                                <select name="{{ $field }}" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach ($opts as $o)
                                        <option value="{{ $o }}" @selected(request($field)===$o)>{{ ucfirst(str_replace('_',' ',$o)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endforeach
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Tag</label>
                                <select name="tag" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach ($tags as $t)
                                        <option value="{{ $t }}" @selected(request('tag')===$t)>{{ $t }}</option>
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
                                <button type="submit" class="flex-1 bg-indigo-600 text-white text-xs py-2 rounded-md hover:bg-indigo-700">Apply</button>
                                <a href="/?space={{ $space->id }}" class="flex-1 text-center bg-gray-100 text-gray-700 text-xs py-2 rounded-md">Reset</a>
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
                                        @foreach (['Task','Assignee','Priority','Status','Tag','Due','Est.'] as $h)
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $h }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($tasks as $task)
                                        @php
                                            $pc = ['urgent'=>'bg-red-100 text-red-700','high'=>'bg-orange-100 text-orange-700','normal'=>'bg-blue-100 text-blue-700','low'=>'bg-gray-100 text-gray-600'][$task->priority];
                                            $sc = ['open'=>'bg-gray-100 text-gray-600','in_progress'=>'bg-blue-100 text-blue-700','in_review'=>'bg-yellow-100 text-yellow-700','closed'=>'bg-green-100 text-green-700'][$task->status];
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">{{ $task->title }}</td>
                                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $task->assignee }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pc }}">{{ ucfirst($task->priority) }}</span></td>
                                            <td class="px-4 py-3 whitespace-nowrap"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span></td>
                                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $task->tag }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($task->due_date)->format('M d') }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $task->time_estimate }}m</td>
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
