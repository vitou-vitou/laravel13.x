<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-6 h-6 rounded" style="background: {{ $board->color }}"></div>
            <h2 class="font-semibold text-xl text-gray-800">{{ $board->name }}</h2>
            <a href="{{ route('boards.index') }}" class="ml-auto text-sm text-gray-400 hover:text-gray-600">← All Boards</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full px-4 sm:px-6 lg:px-8">
            <div class="flex gap-4 overflow-x-auto pb-4">
                @foreach ($board->lists as $list)
                    <div class="flex-shrink-0 w-64 bg-gray-100 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-sm text-gray-700">{{ $list->name }}</h3>
                            <span class="text-xs text-gray-400 bg-white rounded-full px-2 py-0.5">{{ $list->cards->count() }}</span>
                        </div>
                        <div class="space-y-2">
                            @foreach ($list->cards as $card)
                                <div class="bg-white rounded-lg shadow-sm p-3">
                                    @if ($card->label)
                                        @php
                                            $labelColors = ['bug'=>'bg-red-100 text-red-700','design'=>'bg-purple-100 text-purple-700','docs'=>'bg-blue-100 text-blue-700','devops'=>'bg-gray-100 text-gray-700','research'=>'bg-yellow-100 text-yellow-700','chore'=>'bg-gray-100 text-gray-600','content'=>'bg-green-100 text-green-700','task'=>'bg-indigo-100 text-indigo-700'];
                                            $lc = $labelColors[$card->label] ?? 'bg-gray-100 text-gray-600';
                                        @endphp
                                        <span class="inline-block text-xs px-2 py-0.5 rounded-full {{ $lc }} mb-1">{{ $card->label }}</span>
                                    @endif
                                    <p class="text-sm font-medium text-gray-800">{{ $card->title }}</p>
                                    @if ($card->description)
                                        <p class="text-xs text-gray-400 mt-1">{{ $card->description }}</p>
                                    @endif
                                    @if ($card->due_date)
                                        <p class="text-xs text-gray-400 mt-2">📅 {{ $card->due_date->format('M d') }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
