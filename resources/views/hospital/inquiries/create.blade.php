@extends('hospital.layout')

@section('title', 'Submit Hospital Inquiry or Service Request')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="border-b border-slate-200 pb-4">
        <a href="{{ route('hospital.home') }}" class="text-xs text-teal-700 font-semibold hover:underline inline-flex items-center gap-1 mb-2">
            &larr; Back to Patient Portal
        </a>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Submit a Patient Inquiry or Service Request</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Our customer service desk handles patient assistance, appointment clarifications, billing questions, and care feedback.
        </p>
    </div>

    <!-- Notice Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-xs text-blue-900 flex items-start gap-3">
        <span class="text-base">&#9432;</span>
        <div>
            <span class="font-bold">No login required:</span> Upon submitting, you will immediately receive a confidential <span class="font-mono font-semibold">HOSP-YYYY-XXXXX</span> reference code to check answers from our hospital staff.
        </div>
    </div>

    <form action="{{ route('hospital.inquiry.store') }}" method="POST" class="bg-white border border-slate-200 rounded-xl p-6 sm:p-8 shadow-sm space-y-6">
        @csrf

        <!-- Patient Info -->
        <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 text-teal-700">1. Patient / Contact Information</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="patient_name" class="block text-xs font-semibold text-slate-700 mb-1">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="patient_name"
                        id="patient_name"
                        value="{{ old('patient_name') }}"
                        required
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none @error('patient_name') border-red-500 @else border-slate-300 @enderror"
                        placeholder="e.g. John Doe or Guardian Name"
                    >
                    @error('patient_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">
                        Email Address
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none @error('email') border-red-500 @else border-slate-300 @enderror"
                        placeholder="patient@example.com"
                    >
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">
                        Phone Number
                    </label>
                    <input
                        type="tel"
                        name="phone"
                        id="phone"
                        value="{{ old('phone') }}"
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none @error('phone') border-red-500 @else border-slate-300 @enderror"
                        placeholder="+1 (555) 000-0000"
                    >
                    @error('phone')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2">* Provide at least an email or phone so the support desk can follow up.</p>
        </div>

        <hr class="border-slate-200">

        <!-- Inquiry Details -->
        <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 text-teal-700">2. Request Classification</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category" class="block text-xs font-semibold text-slate-700 mb-1">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="category"
                        id="category"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white"
                        required
                    >
                        <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>General Question & Inquiries</option>
                        <option value="appointment" {{ old('category') == 'appointment' ? 'selected' : '' }}>Appointment Booking & Schedule</option>
                        <option value="billing" {{ old('category') == 'billing' ? 'selected' : '' }}>Billing, Insurance & Payments</option>
                        <option value="records" {{ old('category') == 'records' ? 'selected' : '' }}>Medical Records & Scan Reports</option>
                        <option value="pharmacy" {{ old('category') == 'pharmacy' ? 'selected' : '' }}>Pharmacy & Medication Query</option>
                        <option value="complaint" {{ old('category') == 'complaint' ? 'selected' : '' }}>Service Complaint / Patient Care Issue</option>
                    </select>
                </div>

                <div>
                    <label for="department_id" class="block text-xs font-semibold text-slate-700 mb-1">
                        Target Department (Optional)
                    </label>
                    <select
                        name="department_id"
                        id="department_id"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white"
                    >
                        <option value="">-- General Customer Desk Triage --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (old('department_id') == $dept->id || request('dept') == $dept->id) ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-2">
                        Urgency Level <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="flex items-center gap-2 border border-slate-200 rounded-lg p-3 cursor-pointer hover:bg-slate-50 text-xs">
                            <input type="radio" name="urgency" value="routine" {{ old('urgency', 'routine') === 'routine' ? 'checked' : '' }} class="text-teal-600 focus:ring-teal-500">
                            <div>
                                <span class="font-bold text-slate-800 block">Routine</span>
                                <span class="text-[11px] text-slate-500">Normal 24h response</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 border border-amber-200 rounded-lg p-3 cursor-pointer hover:bg-amber-50 text-xs">
                            <input type="radio" name="urgency" value="urgent" {{ old('urgency') === 'urgent' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="font-bold text-amber-900 block">Urgent</span>
                                <span class="text-[11px] text-amber-700">Same-day review</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 border border-red-200 rounded-lg p-3 cursor-pointer hover:bg-red-50 text-xs">
                            <input type="radio" name="urgency" value="critical" {{ old('urgency') === 'critical' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                            <div>
                                <span class="font-bold text-red-900 block">Critical</span>
                                <span class="text-[11px] text-red-700">Priority triage</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-slate-200">

        <!-- Message Details -->
        <div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 text-teal-700">3. Details of Your Inquiry</h3>
            <div class="space-y-4">
                <div>
                    <label for="subject" class="block text-xs font-semibold text-slate-700 mb-1">
                        Subject / Summary <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="subject"
                        id="subject"
                        value="{{ old('subject') }}"
                        required
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none @error('subject') border-red-500 @else border-slate-300 @enderror"
                        placeholder="Brief summary of your question or issue"
                    >
                    @error('subject')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-xs font-semibold text-slate-700 mb-1">
                        Detailed Message <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        name="message"
                        id="message"
                        rows="5"
                        required
                        class="w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-teal-500 focus:outline-none @error('message') border-red-500 @else border-slate-300 @enderror"
                        placeholder="Please include relevant doctor name, clinic date, invoice number, or specific assistance required..."
                    >{{ old('message') }}</textarea>
                    @error('message')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="pt-4 flex items-center justify-between">
            <span class="text-xs text-slate-400">By submitting, your data is processed according to patient privacy standards.</span>
            <button
                type="submit"
                class="px-6 py-2.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow transition"
            >
                Submit Inquiry &rarr;
            </button>
        </div>
    </form>
</div>
@endsection