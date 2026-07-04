@props(['max' => 'max-w-5xl'])

@if (session('status'))
    <div {{ $attributes->merge(['class' => 'mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800']) }}>
        {{ session('status') }}
    </div>
@endif
