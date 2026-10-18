<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\HospitalDepartment;
use App\Models\HospitalFaq;
use App\Models\HospitalInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalInquiryController extends Controller
{
    public function index(): View
    {
        $departments = HospitalDepartment::where('is_active', true)->take(6)->get();
        $faqs = HospitalFaq::where('is_published', true)->orderBy('display_order')->take(4)->get();
        $openCount = HospitalInquiry::whereIn('status', ['open', 'in_progress'])->count();

        return view('hospital.index', compact('departments', 'faqs', 'openCount'));
    }

    public function create(): View
    {
        $departments = HospitalDepartment::where('is_active', true)->orderBy('name')->get();

        return view('hospital.inquiries.create', compact('departments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'category' => ['required', 'string', 'in:general,appointment,billing,complaint,records,pharmacy'],
            'urgency' => ['required', 'string', 'in:routine,urgent,critical'],
            'department_id' => ['nullable', 'exists:hospital_departments,id'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        if (empty($validated['email']) && empty($validated['phone'])) {
            return back()
                ->withInput()
                ->withErrors(['contact' => 'Please provide either an email or a phone number so customer care can reach you.']);
        }

        $inquiry = HospitalInquiry::create([
            'ticket_code' => HospitalInquiry::generateTicketCode(),
            'patient_name' => $validated['patient_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'category' => $validated['category'],
            'urgency' => $validated['urgency'],
            'department_id' => $validated['department_id'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => HospitalInquiry::STATUS_OPEN,
        ]);

        return redirect()
            ->route('hospital.track', ['code' => $inquiry->ticket_code])
            ->with('success', "Your inquiry has been submitted! Reference Code: {$inquiry->ticket_code}. Save this code to track updates.");
    }

    public function track(Request $request): View
    {
        $code = trim((string) $request->input('code'));
        $inquiry = null;
        $searched = false;

        if ($code !== '') {
            $searched = true;
            $inquiry = HospitalInquiry::with(['department', 'publicResponses'])
                ->where('ticket_code', strtoupper($code))
                ->first();
        }

        return view('hospital.inquiries.track', compact('inquiry', 'code', 'searched'));
    }

    public function departments(): View
    {
        $departments = HospitalDepartment::where('is_active', true)->orderBy('name')->get();

        return view('hospital.departments', compact('departments'));
    }

    public function faq(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $query = HospitalFaq::where('is_published', true)->orderBy('display_order');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $faqs = $query->get()->groupBy('category');

        return view('hospital.faq', compact('faqs', 'search'));
    }
}