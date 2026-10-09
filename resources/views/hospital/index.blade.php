@extends('hospital.layout')

@section('title', 'Welcome to Patient & Visitor Customer Care')

@section('content')
<div class="space-y-10">
    <!-- Hero Section -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-800 to-slate-900 text-white p-8 md:p-12 shadow-lg">
        <div class="relative z-10 max-w-3xl space-y-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-teal-500/20 text-teal-200 border border-teal-400/30">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Live Customer Support Active
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                Compassionate Hospital Care & Patient Support
            </h1>
            <p class="text-slate-300 text-sm sm:text-base leading-relaxed">
                Skip the front-desk lines. Submit appointment inquiries, clarify billing and insurance items, request medical documentation, or reach out to patient advocates online 24/7.
            </p>

            <!-- Quick Track Bar -->
            <form action="{{ route('hospital.track') }}" method="GET" class="mt-6 flex flex-col sm:flex-row gap-2 max-w-xl">
                <input
                    type="text"
                    name="code"
                    placeholder="Enter Ticket Reference (e.g. HOSP-2026-DEMO1)"
                    class="flex-1 px-4 py-3 rounded-lg text-slate-800 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-teal-400 bg-white placeholder-slate-400 shadow"
                    required
                >
                <button type="submit" class="px-6 py-3 rounded-lg bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-sm transition shadow">
                    Check Status
                </button>
            </form>
        </div>
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none hidden lg:block">
            <svg class="w-96 h-96 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 10.5h-4.5V6a1.5 1.5 0 0 0-3 0v4.5H7a1.5 1.5 0 0 0 0 3h4.5V18a1.5 1.5 0 0 0 3 0v-4.5H19a1.5 1.5 0 0 0 0-3z"/>
            </svg>
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('hospital.inquiry.create') }}" class="group block p-6 rounded-xl bg-white border border-slate-200 hover:border-teal-500 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xl mb-4 group-hover:scale-105 transition">
                &plus;
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1 group-hover:text-teal-700 transition">Submit New Inquiry</h3>
            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                Have a question regarding appointments, medical records, billing, or general care? Open an instant ticket.
            </p>
            <span class="inline-flex items-center text-xs font-semibold text-teal-700 group-hover:underline">
                Create request &rarr;
            </span>
        </a>

        <a href="{{ route('hospital.track') }}" class="group block p-6 rounded-xl bg-white border border-slate-200 hover:border-teal-500 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xl mb-4 group-hover:scale-105 transition">
                &#128269;
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1 group-hover:text-blue-700 transition">Track Ticket & Responses</h3>
            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                Lookup staff answers and check the status of your existing inquiry anytime using your tracking code.
            </p>
            <span class="inline-flex items-center text-xs font-semibold text-blue-700 group-hover:underline">
                Lookup ticket &rarr;
            </span>
        </a>

        <a href="{{ route('hospital.departments') }}" class="group block p-6 rounded-xl bg-white border border-slate-200 hover:border-teal-500 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xl mb-4 group-hover:scale-105 transition">
                &#127973;
            </div>
            <h3 class="font-bold text-slate-900 text-base mb-1 group-hover:text-purple-700 transition">Departments & Contacts</h3>
            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                Find outpatient clinics, pharmacy counters, billing desks, visiting hours, and direct line telephone numbers.
            </p>
            <span class="inline-flex items-center text-xs font-semibold text-purple-700 group-hover:underline">
                View hospital units &rarr;
            </span>
        </a>
    </div>

    <!-- Active Departments Spotlight -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Hospital Departments Directory</h2>
                <p class="text-xs text-slate-500">Quick direct contacts and locations across the campus</p>
            </div>
            <a href="{{ route('hospital.departments') }}" class="text-xs text-teal-700 font-semibold hover:underline">
                View All Departments &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($departments as $dept)
                <div class="border border-slate-100 rounded-lg p-4 bg-slate-50/50 hover:bg-white hover:border-slate-300 transition">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-teal-100 text-teal-800">{{ $dept->code ?? 'DEPT' }}</span>
                        <span class="text-[11px] text-slate-400 font-medium">{{ $dept->operating_hours }}</span>
                    </div>
                    <h4 class="font-semibold text-slate-900 text-sm mb-1">{{ $dept->name }}</h4>
                    <p class="text-xs text-slate-500 mb-3">{{ $dept->location }}</p>
                    <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                        <a href="tel:{{ $dept->phone }}" class="text-teal-700 font-semibold hover:underline">
                            {{ $dept->phone }}
                        </a>
                        <a href="{{ route('hospital.inquiry.create') }}?dept={{ $dept->id }}" class="text-slate-500 hover:text-slate-800">
                            Ask Desk &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Common FAQs Highlight -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Patient & Visitor Frequently Asked Questions</h2>
                <p class="text-xs text-slate-500">Instant answers to routine hospital inquiries</p>
            </div>
            <a href="{{ route('hospital.faq') }}" class="text-xs text-teal-700 font-semibold hover:underline">
                Browse Full FAQ &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($faqs as $faq)
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-100">
                    <div class="text-[11px] font-bold text-teal-700 uppercase tracking-wider mb-1">{{ $faq->category }}</div>
                    <h4 class="text-sm font-semibold text-slate-900 mb-2">{{ $faq->question }}</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $faq->answer }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection