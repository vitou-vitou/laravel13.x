<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Boards</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($boards as $board)
                    <a href="{{ route('boards.show', $board) }}"
                       class="block bg-white rounded-xl shadow hover:shadow-md transition p-5 border-t-4"
                       style="border-color: {{ $board->color }}">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-bold"
                                 style="background: {{ $board->color }}">
                                {{ strtoupper(substr($board->name, 0, 1)) }}
                            </div>
                            <span class="font-semibold text-gray-800">{{ $board->name }}</span>
                        </div>
                        <p class="text-xs text-gray-400">{{ $board->lists_count }} lists</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
