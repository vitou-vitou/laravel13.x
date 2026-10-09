@extends('hospital.layout')

@section('title', 'Track Inquiry Status')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <div class="border-b border-slate-200 pb-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Track Hospital Ticket Status</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Check live customer service updates, assigned departments, and staff responses.
        </p>
    </div>

    <!-- Tracking Lookup Box -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <form action="{{ route('hospital.track') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <label for="code" class="block text-xs font-semibold text-slate-700 mb-1">Enter Inquiry Reference Code</label>
                <input
                    type="text"
                    name="code"
                    id="code"
                    value="{{ $code ?? '' }}"
                    placeholder="e.g. HOSP-2026-DEMO1"
                    class="w-full px-4 py-2.5 text-sm uppercase tracking-wider font-mono border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                    required
                >
            </div>
            <div class="sm:self-end">
                <button
                    type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow transition"
                >
                    Search Ticket
                </button>
            </div>
        </form>
    </div>

    @if($searched && !$inquiry)
        <!-- Not Found Alert -->
        <div class="rounded-xl border border-red-200 bg-red-50 p-6 text-center space-y-3">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto text-xl font-bold">
                !
            </div>
            <h3 class="font-bold text-red-900 text-base">Inquiry Not Found</h3>
            <p class="text-xs text-red-700 max-w-md mx-auto">
                No hospital record found with code <span class="font-mono font-bold">{{ $code }}</span>. Please double-check your confirmation code or submit a new inquiry.
            </p>
            <div class="pt-2">
                <a href="{{ route('hospital.inquiry.create') }}" class="inline-block text-xs font-bold text-red-800 bg-red-100 hover:bg-red-200 px-4 py-2 rounded-lg transition">
                    Submit New Inquiry &rarr;
                </a>
            </div>
        </div>
    @elseif($inquiry)
        <!-- Ticket Found Card -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm space-y-6 p-6 sm:p-8">
            <!-- Header bar with Status -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 gap-4">
                <div>
                    <span class="text-xs font-semibold text-slate-400">REFERENCE NUMBER</span>
                    <h2 class="text-2xl font-black font-mono tracking-tight text-slate-900">{{ $inquiry->ticket_code }}</h2>
                    <p class="text-xs text-slate-500 mt-1">Submitted on {{ $inquiry->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Status Badge -->
                    @if($inquiry->status === 'open')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Open &bull; Awaiting Review
                        </span>
                    @elseif($inquiry->status === 'in_progress')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span> In Progress &bull; Under Action
                        </span>
                    @elseif($inquiry->status === 'resolved')
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Resolved
                        </span>
                    @else
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            Closed
                        </span>
                    @endif

                    <!-- Urgency -->
                    <span class="px-2.5 py-1 rounded text-xs font-semibold uppercase tracking-wider
                        @if($inquiry->urgency === 'critical') bg-red-100 text-red-800
                        @elseif($inquiry->urgency === 'urgent') bg-amber-100 text-amber-800
                        @else bg-slate-100 text-slate-700 @endif">
                        {{ $inquiry->urgency }}
                    </span>
                </div>
            </div>

            <!-- Ticket Metadata Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-lg text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Patient / Contact</span>
                    <span class="font-bold text-slate-800">{{ $inquiry->patient_name }}</span>
                    @if($inquiry->email)<div class="text-slate-500">{{ $inquiry->email }}</div>@endif
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Category</span>
                    <span class="font-bold text-slate-800 capitalize">{{ $inquiry->category }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">Assigned Department</span>
                    <span class="font-bold text-teal-700">
                        {{ $inquiry->department ? $inquiry->department->name : 'Customer Service General Desk' }}
                    </span>
                </div>
            </div>

            <!-- Subject & Message -->
            <div class="space-y-2">
                <span class="text-xs font-bold uppercase text-slate-400">Inquiry Subject</span>
                <h3 class="text-base font-bold text-slate-900">{{ $inquiry->subject }}</h3>
                <div class="bg-white border border-slate-200 rounded-lg p-4 text-xs text-slate-700 whitespace-pre-line leading-relaxed">
                    {{ $inquiry->message }}
                </div>
            </div>

            <!-- Hospital Staff Responses Section -->
            <div class="space-y-4 pt-4 border-t border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>Staff Responses & Resolution Updates</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 font-semibold">
                            {{ $inquiry->publicResponses->count() }}
                        </span>
                    </h3>
                </div>

                @if($inquiry->publicResponses->isEmpty())
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-6 text-center text-xs text-slate-500">
                        Our customer service team is currently reviewing your ticket. Staff responses will appear here shortly.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($inquiry->publicResponses as $response)
                            <div class="bg-teal-50/50 border border-teal-200 rounded-lg p-4 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-teal-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-teal-600"></span>
                                        {{ $response->author_name }}
                                    </span>
                                    <span class="text-slate-400">{{ $response->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-xs text-slate-800 whitespace-pre-line leading-relaxed">
                                    {{ $response->response_text }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($inquiry->resolved_at)
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-xs text-emerald-800 flex items-center gap-2">
                    <span class="font-bold">&#10003; Marked Resolved:</span>
                    <span>This ticket was resolved on {{ $inquiry->resolved_at->format('M d, Y \a\t h:i A') }}.</span>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection