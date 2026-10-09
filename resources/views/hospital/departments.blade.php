@extends('hospital.layout')

@section('title', 'Hospital Departments Directory')

@section('content')
<div class="space-y-6">
    <div class="border-b border-slate-200 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Hospital Departments & Clinic Directory</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Direct contacts, building locations, and operating hours for St. Jude Medical Center units.
            </p>
        </div>
        <div>
            <a href="{{ route('hospital.inquiry.create') }}" class="px-4 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow transition">
                + Inquire With Department
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($departments as $dept)
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm flex flex-col justify-between hover:border-teal-500 transition">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-800 border border-teal-200">
                            {{ $dept->code ?? 'UNIT' }}
                        </span>
                        <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                            {{ $dept->operating_hours }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-slate-900">{{ $dept->name }}</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">{{ $dept->description }}</p>

                    <div class="pt-2 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 font-medium w-16">Location:</span>
                            <span class="font-semibold text-slate-800">{{ $dept->location }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 font-medium w-16">Phone:</span>
                            <a href="tel:{{ $dept->phone }}" class="font-semibold text-teal-700 hover:underline">{{ $dept->phone }}</a>
                        </div>
                        @if($dept->email)
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 font-medium w-16">Email:</span>
                            <a href="mailto:{{ $dept->email }}" class="text-slate-700 hover:underline">{{ $dept->email }}</a>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-end">
                    <a href="{{ route('hospital.inquiry.create') }}?dept={{ $dept->id }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">
                        Send Question &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection