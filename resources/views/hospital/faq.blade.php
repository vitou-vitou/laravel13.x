@extends('hospital.layout')

@section('title', 'Frequently Asked Questions')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="border-b border-slate-200 pb-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Patient & Visitor Knowledge Base</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Instant answers for visiting policies, insurance, appointments, and care services.
        </p>
    </div>

    <!-- Search Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
        <form action="{{ route('hospital.faq') }}" method="GET" class="flex gap-2">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search topics (e.g., insurance, records, visiting, appointment)..."
                class="flex-1 px-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
            >
            <button type="submit" class="px-5 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition">
                Search
            </button>
            @if(!empty($search))
                <a href="{{ route('hospital.faq') }}" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Categories & FAQ items -->
    @if($faqs->isEmpty())
        <div class="bg-white border border-slate-200 rounded-xl p-8 text-center space-y-3">
            <p class="text-sm text-slate-600">No questions matched your search query "{{ $search }}".</p>
            <a href="{{ route('hospital.inquiry.create') }}" class="inline-block text-xs font-bold text-teal-700 hover:underline">
                Ask our customer service desk directly &rarr;
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($faqs as $category => $items)
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                    <h2 class="text-sm font-bold text-teal-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>{{ $category }}</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-medium">{{ count($items) }} answers</span>
                    </h2>
                    <div class="space-y-4 divide-y divide-slate-100">
                        @foreach($items as $item)
                            <div class="pt-3 first:pt-0">
                                <h3 class="text-sm font-bold text-slate-900 mb-1.5 flex items-start gap-2">
                                    <span class="text-teal-600 font-black">Q:</span>
                                    <span>{{ $item->question }}</span>
                                </h3>
                                <p class="text-xs text-slate-600 pl-5 leading-relaxed">{{ $item->answer }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection