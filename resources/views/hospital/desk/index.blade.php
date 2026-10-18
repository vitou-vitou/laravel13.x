@extends('hospital.layout')

@section('title', 'Hospital Customer Service Desk')

@section('content')
<div class="space-y-6">
    <!-- Desk Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-xs font-black uppercase tracking-wider bg-slate-900 text-white">Staff Desk</span>
                <span class="text-xs text-slate-400">&bull; Patient Relations & Inquiries Management</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Hospital Customer Service Dashboard</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('hospital.inquiry.create') }}" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow transition">
                + New Walk-in / Phone Ticket
            </a>
            <a href="{{ route('hospital.home') }}" class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition">
                Patient View &rarr;
            </a>
        </div>
    </div>

    <!-- Triage Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('hospital.desk.index', ['status' => 'open']) }}" class="p-4 rounded-xl bg-white border border-slate-200 hover:border-amber-400 transition shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Open Tickets</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $metrics['total_open'] }}</div>
            <span class="text-[11px] text-slate-500">Awaiting initial review</span>
        </a>

        <a href="{{ route('hospital.desk.index', ['status' => 'in_progress']) }}" class="p-4 rounded-xl bg-white border border-slate-200 hover:border-blue-400 transition shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">In Progress</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $metrics['in_progress'] }}</div>
            <span class="text-[11px] text-slate-500">Under investigation</span>
        </a>

        <a href="{{ route('hospital.desk.index', ['urgency' => 'urgent']) }}" class="p-4 rounded-xl bg-white border border-slate-200 hover:border-red-400 transition shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Urgent / Critical</span>
            <div class="text-2xl font-black text-red-600 mt-1">{{ $metrics['urgent_pending'] }}</div>
            <span class="text-[11px] text-slate-500">High priority inquiries</span>
        </a>

        <a href="{{ route('hospital.desk.index', ['status' => 'resolved']) }}" class="p-4 rounded-xl bg-white border border-slate-200 hover:border-emerald-400 transition shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Resolved</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $metrics['resolved'] }}</div>
            <span class="text-[11px] text-slate-500">Completed inquiries</span>
        </a>
    </div>

    <!-- Filters and Search Toolbar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm space-y-3">
        <!-- Status Pills -->
        <div class="flex flex-wrap items-center gap-2 pb-3 border-b border-slate-100 text-xs font-semibold">
            <span class="text-slate-400 mr-2">Status:</span>
            <a href="{{ route('hospital.desk.index', array_merge(request()->query(), ['status' => 'all'])) }}"
               class="px-3 py-1 rounded-full {{ $status === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                All ({{ $inquiries->total() }})
            </a>
            <a href="{{ route('hospital.desk.index', array_merge(request()->query(), ['status' => 'open'])) }}"
               class="px-3 py-1 rounded-full {{ $status === 'open' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                Open
            </a>
            <a href="{{ route('hospital.desk.index', array_merge(request()->query(), ['status' => 'in_progress'])) }}"
               class="px-3 py-1 rounded-full {{ $status === 'in_progress' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-800 hover:bg-blue-100' }}">
                In Progress
            </a>
            <a href="{{ route('hospital.desk.index', array_merge(request()->query(), ['status' => 'resolved'])) }}"
               class="px-3 py-1 rounded-full {{ $status === 'resolved' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                Resolved
            </a>
            <a href="{{ route('hospital.desk.index', array_merge(request()->query(), ['status' => 'closed'])) }}"
               class="px-3 py-1 rounded-full {{ $status === 'closed' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Closed
            </a>
        </div>

        <!-- Filter Form -->
        <form action="{{ route('hospital.desk.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <input type="hidden" name="status" value="{{ $status }}">

            <div>
                <label class="block text-slate-500 font-medium mb-1">Search Keywords</label>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Code, patient name, subject..."
                    class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none"
                >
            </div>

            <div>
                <label class="block text-slate-500 font-medium mb-1">Urgency</label>
                <select name="urgency" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
                    <option value="all">All Urgencies</option>
                    <option value="routine" {{ $urgency === 'routine' ? 'selected' : '' }}>Routine</option>
                    <option value="urgent" {{ $urgency === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="critical" {{ $urgency === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-500 font-medium mb-1">Department</label>
                <select name="department_id" class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold transition">
                    Filter
                </button>
                <a href="{{ route('hospital.desk.index') }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Inquiries Table -->
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-semibold">
                    <tr>
                        <th class="px-4 py-3">Ticket Code</th>
                        <th class="px-4 py-3">Patient</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Urgency</th>
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Submitted</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inquiries as $inquiry)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                <a href="{{ route('hospital.desk.show', $inquiry->ticket_code) }}" class="text-teal-700 hover:underline">
                                    {{ $inquiry->ticket_code }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-800">{{ $inquiry->patient_name }}</div>
                                <div class="text-[11px] text-slate-400">{{ $inquiry->phone ?? $inquiry->email }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="capitalize px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
                                    {{ $inquiry->category }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase
                                    @if($inquiry->urgency === 'critical') bg-red-100 text-red-800
                                    @elseif($inquiry->urgency === 'urgent') bg-amber-100 text-amber-800
                                    @else bg-slate-100 text-slate-600 @endif">
                                    {{ $inquiry->urgency }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $inquiry->department ? $inquiry->department->name : 'General Care Desk' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($inquiry->status === 'open')
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">Open</span>
                                @elseif($inquiry->status === 'in_progress')
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800">In Progress</span>
                                @elseif($inquiry->status === 'resolved')
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">Resolved</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">Closed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400 whitespace-nowrap">
                                {{ $inquiry->created_at->format('M d, h:i A') }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('hospital.desk.show', $inquiry->ticket_code) }}"
                                   class="inline-block px-3 py-1 rounded bg-teal-50 hover:bg-teal-600 text-teal-700 hover:text-white font-bold transition">
                                    Manage &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                No inquiries match the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($inquiries->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $inquiries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection