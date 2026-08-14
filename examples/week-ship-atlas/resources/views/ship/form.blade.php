@extends('layouts.app')

@section('title', ($note->exists ? 'Edit' : 'New').' note — Week Ship Atlas')

@section('content')
    <section class="mb-6 max-w-xl">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $note->exists ? 'Edit note' : 'New ship note' }}</h1>
        <p class="mt-2 text-stone-600">Region × company habit × project type + practice evidence.</p>
    </section>

    <form
        method="post"
        action="{{ $note->exists ? route('ship.update', $note) : route('ship.store') }}"
        class="max-w-xl space-y-4 rounded-lg border border-stone-300 bg-white p-5"
    >
        @csrf
        @if ($note->exists)
            @method('PUT')
        @endif

        <div>
            <label class="mb-1 block text-sm font-medium" for="weekday">Weekday</label>
            <select id="weekday" name="weekday" class="w-full rounded-md border border-stone-300 px-3 py-2">
                @foreach ($weekdays as $key => $label)
                    <option value="{{ $key }}" @selected(old('weekday', $note->weekday) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="title">Title</label>
            <input id="title" name="title" type="text" value="{{ old('title', $note->title) }}" required
                   class="w-full rounded-md border border-stone-300 px-3 py-2"
                   placeholder="SEA × Shape Up × Admin CRUD">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="region">Region</label>
            <input id="region" name="region" type="text" value="{{ old('region', $note->region) }}" required
                   class="w-full rounded-md border border-stone-300 px-3 py-2"
                   placeholder="Southeast Asia">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="company_habit">Company habit</label>
            <input id="company_habit" name="company_habit" type="text" value="{{ old('company_habit', $note->company_habit) }}" required
                   class="w-full rounded-md border border-stone-300 px-3 py-2"
                   placeholder="Shape / calm product — fixed appetite">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="project_type">Project type</label>
            <input id="project_type" name="project_type" type="text" value="{{ old('project_type', $note->project_type) }}" required
                   class="w-full rounded-md border border-stone-300 px-3 py-2"
                   placeholder="Internal tools / insurance admin CRUD">
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="practice">Practice evidence</label>
            <textarea id="practice" name="practice" rows="4"
                      class="w-full rounded-md border border-stone-300 px-3 py-2"
                      placeholder="What you tried in code today…">{{ old('practice', $note->practice) }}</textarea>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="verdict">Verdict</label>
            <select id="verdict" name="verdict" class="w-full rounded-md border border-stone-300 px-3 py-2">
                @foreach ($verdicts as $verdict)
                    <option value="{{ $verdict }}" @selected(old('verdict', $note->verdict) === $verdict)>{{ $verdict }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="rounded-md bg-teal-800 px-4 py-2 text-sm font-medium text-white hover:bg-teal-900">
                Save
            </button>
            <a href="{{ route('ship.index') }}" class="rounded-md border border-stone-300 px-4 py-2 text-sm text-stone-700">Cancel</a>
        </div>
    </form>
@endsection
