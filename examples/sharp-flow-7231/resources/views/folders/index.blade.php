<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ $folder->icon }} {{ $folder->name }}</h2>
    </x-slot>

    <div class="py-6" x-data="{ filtersOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Folder tabs --}}
            <div class="flex gap-2 mb-5 overflow-x-auto">
                @foreach ($folders as $f)
                    <a href="/?folder={{ $f->id }}"
                       class="flex-shrink-0 flex items-center gap-1 px-3 py-1.5 rounded-full text-sm font-medium transition {{ $f->id == $folder->id ? $f->color . ' text-white' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm' }}">
                        {{ $f->icon }} {{ $f->name }}
                    </a>
                @endforeach
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-5">
                @foreach ([['Active',$stats['active'],'bg-blue-50 text-blue-700 border-blue-200'],['Deferred',$stats['deferred'],'bg-yellow-50 text-yellow-700 border-yellow-200'],['Completed',$stats['completed'],'bg-green-50 text-green-700 border-green-200']] as [$l,$v,$cl])
                <div class="border rounded-lg p-3 text-center {{ $cl }}">
                    <div class="text-xs text-gray-500">{{ $l }}</div>
                    <div class="text-2xl font-bold">{{ $v }}</div>
                </div>
                @endforeach
            </div>

            <div class="lg:hidden mb-3">
                <button @click="filtersOpen = !filtersOpen" class="flex items-center gap-2 text-sm font-medium text-blue-600 border border-blue-300 rounded-md px-3 py-2 hover:bg-blue-50">
                    <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-5">
                <aside class="w-full lg:w-48 lg:flex-shrink-0 lg:!block" x-show="filtersOpen" x-transition>
                    <div class="bg-white rounded-lg shadow p-4">
                        <form method="GET" action="/">
                            <input type="hidden" name="folder" value="{{ $folder->id }}">
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Status</label>
                                <select name="status" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach (['active'=>'Active','completed'=>'Completed','deferred'=>'Deferred','cancelled'=>'Cancelled'] as $v=>$l)
                                        <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Importance</label>
                                <select name="importance" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach (['high','normal','low'] as $o)
                                        <option value="{{ $o }}" @selected(request('importance')===$o)>{{ ucfirst($o) }}</option>
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
                                <button type="submit" class="flex-1 bg-blue-600 text-white text-xs py-2 rounded-md hover:bg-blue-700">Apply</button>
                                <a href="/?folder={{ $folder->id }}" class="flex-1 text-center bg-gray-100 text-gray-700 text-xs py-2 rounded-md">Reset</a>
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
                                        @foreach (['Task','Status','Importance','Assignee','Start','Due','Effort'] as $h)
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $h }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($tasks as $task)
                                    @php
                                        $sc = ['active'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','deferred'=>'bg-yellow-100 text-yellow-700','cancelled'=>'bg-red-100 text-red-600'][$task->status];
                                        $ic = ['high'=>'bg-red-100 text-red-700','normal'=>'bg-gray-100 text-gray-600','low'=>'bg-green-100 text-green-700'][$task->importance];
                                    @endphp
                                    <tr class="hover:bg-gray-50 {{ $task->status === 'completed' ? 'opacity-60' : '' }}">
                                        <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap {{ $task->status === 'completed' ? 'line-through' : '' }}">{{ $task->title }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $sc }}">{{ ucfirst($task->status) }}</span></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><span class="px-2 py-0.5 rounded text-xs font-medium {{ $ic }}">{{ ucfirst($task->importance) }}</span></td>
                                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $task->assignee ?? '—' }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('M d') : '—' }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d') : '—' }}</td>
                                        <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $task->effort ? $task->effort.'h' : '—' }}</td>
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
