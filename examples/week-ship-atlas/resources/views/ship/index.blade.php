@extends('layouts.app')

@section('title', 'Week board — Week Ship Atlas')

@section('content')
    <section class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight">7-day ship board</h1>
        <p class="mt-2 max-w-2xl text-stone-600">
            Practice one habit a day. Publish Friday only if Thursday left practice evidence.
            This example is the atlas week loop as a tiny Laravel app.
        </p>
    </section>

    <div class="grid gap-4 md:grid-cols-2">
        @foreach ($weekdays as $key => $label)
            @php($dayNotes = $notes->get($key, collect()))
            <article class="rounded-lg border border-stone-300 bg-white p-4">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h2 class="font-medium">{{ $label }}</h2>
                    <span class="text-xs text-stone-500">{{ $dayNotes->count() }} note(s)</span>
                </div>

                @forelse ($dayNotes as $note)
                    <div class="mb-3 rounded-md border border-stone-200 bg-stone-50 p-3 last:mb-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-medium">{{ $note->title }}</p>
                                <p class="mt-1 text-xs text-stone-600">
                                    {{ $note->region }} · {{ $note->company_habit }} · {{ $note->project_type }}
                                </p>
                                @if ($note->practice)
                                    <p class="mt-2 text-sm text-stone-700">{{ $note->practice }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-xs
                                @if ($note->verdict === 'keep') bg-teal-100 text-teal-900
                                @elseif ($note->verdict === 'drop') bg-stone-200 text-stone-700
                                @else bg-amber-100 text-amber-900
                                @endif">
                                {{ $note->verdict }}
                            </span>
                        </div>
                        <div class="mt-3 flex gap-3 text-sm">
                            <a href="{{ route('ship.edit', $note) }}" class="text-teal-800 underline">Edit</a>
                            <form method="post" action="{{ route('ship.destroy', $note) }}" onsubmit="return confirm('Delete this note?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-stone-600 underline">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-stone-500">Empty — add a note for this day.</p>
                @endforelse
            </article>
        @endforeach
    </div>
@endsection
