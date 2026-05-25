<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Projects</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($projects as $project)
                    @php $pct = $project->task_count > 0 ? round(($project->done_count / $project->task_count) * 100) : 0; @endphp
                    <a href="/projects/{{ $project->id }}" class="block bg-white rounded-xl shadow hover:shadow-md transition p-5 border-l-4" style="border-color: {{ $project->color }}">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-3 h-3 rounded-full" style="background: {{ $project->color }}"></div>
                            <span class="font-semibold text-gray-800">{{ $project->name }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>{{ $project->done_count }}/{{ $project->task_count }} tasks done</span>
                            <span>{{ $pct }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full" style="width: {{ $pct }}%; background: {{ $project->color }}"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
