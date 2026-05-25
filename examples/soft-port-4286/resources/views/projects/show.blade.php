<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-4 h-4 rounded-full" style="background: {{ $project->color }}"></div>
            <h2 class="font-semibold text-xl text-gray-800">{{ $project->name }}</h2>
            <a href="/" class="ml-auto text-sm text-gray-400 hover:text-gray-600">← Projects</a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ filtersOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="lg:hidden mb-3">
                <button @click="filtersOpen = !filtersOpen" class="flex items-center gap-2 text-sm font-medium text-indigo-600 border border-indigo-300 rounded-md px-3 py-2 hover:bg-indigo-50">
                    <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">
                <aside class="w-full lg:w-48 lg:flex-shrink-0 lg:!block" x-show="filtersOpen" x-transition>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="font-semibold text-gray-700 mb-3 text-sm">Filters</h3>
                        <form method="GET">
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Status</label>
                                <select name="status" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach (['todo','in_progress','review','done'] as $s)
                                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="block text-xs text-gray-500 mb-1">Priority</label>
                                <select name="priority" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach (['high','medium','low'] as $p)
                                        <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs text-gray-500 mb-1">Assignee</label>
                                <select name="assignee" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach ($assignees as $a)
                                        <option value="{{ $a }}" @selected(request('assignee') === $a)>{{ $a }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-indigo-600 text-white text-xs py-2 rounded-md hover:bg-indigo-700">Apply</button>
                                <a href="/projects/{{ $project->id }}" class="flex-1 text-center bg-gray-100 text-gray-700 text-xs py-2 rounded-md hover:bg-gray-200">Reset</a>
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
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Task</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Assignee</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Priority</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Due</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($tasks as $task)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-800 whitespace-nowrap">{{ $task->title }}</div>
                                                @if($task->description)<div class="text-xs text-gray-400">{{ $task->description }}</div>@endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $task->assignee }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @php $pc = ['high'=>'text-red-600 bg-red-50','medium'=>'text-yellow-600 bg-yellow-50','low'=>'text-green-600 bg-green-50'][$task->priority]; @endphp
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pc }}">{{ ucfirst($task->priority) }}</span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @php $sc = ['todo'=>'bg-gray-100 text-gray-600','in_progress'=>'bg-blue-100 text-blue-700','review'=>'bg-yellow-100 text-yellow-700','done'=>'bg-green-100 text-green-700'][$task->status]; @endphp
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No tasks found.</td></tr>
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
