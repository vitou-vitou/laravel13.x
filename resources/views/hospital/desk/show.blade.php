@extends('hospital.layout')

@section('title', 'Manage Ticket #' . $inquiry->ticket_code)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <a href="{{ route('hospital.desk.index') }}" class="text-xs text-teal-700 font-semibold hover:underline inline-flex items-center gap-1 mb-1">
                &larr; Back to Inquiries Queue
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black font-mono text-slate-900">{{ $inquiry->ticket_code }}</h1>
                <!-- Status Badge -->
                @if($inquiry->status === 'open')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Open</span>
                @elseif($inquiry->status === 'in_progress')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">In Progress</span>
                @elseif($inquiry->status === 'resolved')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Resolved</span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">Closed</span>
                @endif
                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase
                    @if($inquiry->urgency === 'critical') bg-red-100 text-red-800
                    @elseif($inquiry->urgency === 'urgent') bg-amber-100 text-amber-800
                    @else bg-slate-100 text-slate-700 @endif">
                    {{ $inquiry->urgency }}
                </span>
            </div>
        </div>
        <div>
            <a href="{{ route('hospital.track', ['code' => $inquiry->ticket_code]) }}" target="_blank"
               class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-bold inline-flex items-center gap-1">
                View Public Patient Tracker &#8599;
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Patient Profile & Status Control -->
        <div class="space-y-6">
            <!-- Patient Contact Box -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-teal-700">Patient Details</h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-400 block font-medium">Name:</span>
                        <span class="text-sm font-bold text-slate-800">{{ $inquiry->patient_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Phone:</span>
                        @if($inquiry->phone)
                            <a href="tel:{{ $inquiry->phone }}" class="font-semibold text-teal-700 hover:underline">{{ $inquiry->phone }}</a>
                        @else
                            <span class="text-slate-400">Not provided</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Email:</span>
                        @if($inquiry->email)
                            <a href="mailto:{{ $inquiry->email }}" class="font-semibold text-teal-700 hover:underline">{{ $inquiry->email }}</a>
                        @else
                            <span class="text-slate-400">Not provided</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Category:</span>
                        <span class="font-semibold text-slate-800 capitalize">{{ $inquiry->category }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Received:</span>
                        <span class="text-slate-600">{{ $inquiry->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Status & Notes Control Form -->
            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4 text-xs">
                <h3 class="font-bold text-slate-900 uppercase tracking-wider text-teal-700">Triage & Status</h3>

                <form action="{{ route('hospital.desk.status', $inquiry->ticket_code) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label for="status" class="block font-semibold text-slate-700 mb-1">Update Status</label>
                        <select name="status" id="status" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 bg-white font-medium">
                            <option value="open" {{ $inquiry->status === 'open' ? 'selected' : '' }}>Open (Awaiting triage)</option>
                            <option value="in_progress" {{ $inquiry->status === 'in_progress' ? 'selected' : '' }}>In Progress (Under action)</option>
                            <option value="resolved" {{ $inquiry->status === 'resolved' ? 'selected' : '' }}>Resolved (Completed)</option>
                            <option value="closed" {{ $inquiry->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div>
                        <label for="staff_notes" class="block font-semibold text-slate-700 mb-1">Staff Administrative Notes (Internal)</label>
                        <textarea
                            name="staff_notes"
                            id="staff_notes"
                            rows="3"
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                            placeholder="Add brief handover note or insurer reference..."
                        >{{ old('staff_notes', $inquiry->staff_notes) }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold transition">
                        Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Column: Inquiry Content & Responses Timeline -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Patient Inquiry Box -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase">Original Patient Request</span>
                    <span class="text-xs text-slate-400">{{ $inquiry->created_at->diffForHumans() }}</span>
                </div>
                <h3 class="text-base font-bold text-slate-900">{{ $inquiry->subject }}</h3>
                <div class="text-xs text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-lg border border-slate-100">
                    {{ $inquiry->message }}
                </div>
            </div>

            <!-- Responses & Internal Notes Feed -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>Communication History & Notes</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">{{ $inquiry->responses->count() }}</span>
                    </h3>
                </div>

                @if($inquiry->responses->isEmpty())
                    <div class="text-center py-6 text-xs text-slate-400">
                        No responses or internal notes added yet. Use the form below to respond to the patient.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($inquiry->responses as $resp)
                            <div class="p-4 rounded-lg border text-xs space-y-2
                                @if($resp->is_internal)
                                    bg-amber-50/70 border-amber-200 text-amber-950
                                @else
                                    bg-teal-50/50 border-teal-200 text-slate-800
                                @endif">
                                <div class="flex items-center justify-between font-bold">
                                    <div class="flex items-center gap-2">
                                        @if($resp->is_internal)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase bg-amber-200 text-amber-900">Internal Note</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase bg-teal-200 text-teal-900">Public Reply</span>
                                        @endif
                                        <span>{{ $resp->author_name }}</span>
                                    </div>
                                    <span class="text-slate-400 font-normal">{{ $resp->created_at->format('M d, h:i A') }} ({{ $resp->created_at->diffForHumans() }})</span>
                                </div>
                                <div class="whitespace-pre-line leading-relaxed">
                                    {{ $resp->response_text }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Add Response or Note Form -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-teal-700">Add Staff Response or Note</h3>

                <form action="{{ route('hospital.desk.respond', $inquiry->ticket_code) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="author_name" class="block font-semibold text-slate-700 mb-1">
                                Staff Name / Role <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="author_name"
                                id="author_name"
                                value="{{ old('author_name', 'Patient Relations Officer') }}"
                                required
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                            >
                        </div>

                        <div>
                            <label for="new_status" class="block font-semibold text-slate-700 mb-1">
                                Change Status To
                            </label>
                            <select name="new_status" id="new_status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 bg-white">
                                <option value="">-- Keep Current Status ({{ ucfirst($inquiry->status) }}) --</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Mark Resolved</option>
                                <option value="closed">Close Ticket</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="department_id" class="block font-semibold text-slate-700 mb-1">
                            Re-assign Department (Optional)
                        </label>
                        <select name="department_id" id="department_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 bg-white">
                            <option value="">-- Keep ({{ $inquiry->department ? $inquiry->department->name : 'General Care Desk' }}) --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $inquiry->department_id == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="response_text" class="block font-semibold text-slate-700 mb-1">
                            Response Content <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            name="response_text"
                            id="response_text"
                            rows="4"
                            required
                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500"
                            placeholder="Type the message for the patient or internal triage findings..."
                        >{{ old('response_text') }}</textarea>
                    </div>

                    <div class="flex items-center gap-2 p-3 rounded-lg bg-slate-50 border border-slate-200">
                        <input
                            type="checkbox"
                            name="is_internal"
                            id="is_internal"
                            value="1"
                            {{ old('is_internal') ? 'checked' : '' }}
                            class="rounded text-teal-600 focus:ring-teal-500"
                        >
                        <label for="is_internal" class="text-xs text-slate-700 cursor-pointer font-medium">
                            <span class="font-bold text-slate-900">Staff-only internal note</span> &mdash; Check this to keep this note hidden from the public patient tracking view.
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow transition">
                            Submit Response &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection