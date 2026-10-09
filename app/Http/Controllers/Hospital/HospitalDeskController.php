<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\HospitalDepartment;
use App\Models\HospitalInquiry;
use App\Models\HospitalInquiryResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalDeskController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'all');
        $urgency = $request->input('urgency', 'all');
        $departmentId = $request->input('department_id');
        $search = $request->input('search');

        $query = HospitalInquiry::with('department')->latest();

        if ($status !== 'all' && in_array($status, ['open', 'in_progress', 'resolved', 'closed'])) {
            $query->where('status', $status);
        }

        if ($urgency !== 'all' && in_array($urgency, ['routine', 'urgent', 'critical'])) {
            $query->where('urgency', $urgency);
        }

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        if (!empty($search)) {
            $query->search($search);
        }

        $inquiries = $query->paginate(15)->withQueryString();

        $metrics = [
            'total_open' => HospitalInquiry::where('status', 'open')->count(),
            'in_progress' => HospitalInquiry::where('status', 'in_progress')->count(),
            'urgent_pending' => HospitalInquiry::whereIn('status', ['open', 'in_progress'])
                ->whereIn('urgency', ['urgent', 'critical'])
                ->count(),
            'resolved' => HospitalInquiry::where('status', 'resolved')->count(),
        ];

        $departments = HospitalDepartment::where('is_active', true)->orderBy('name')->get();

        return view('hospital.desk.index', compact(
            'inquiries',
            'metrics',
            'departments',
            'status',
            'urgency',
            'departmentId',
            'search'
        ));
    }

    public function show(string $ticket_code): View
    {
        $inquiry = HospitalInquiry::with(['department', 'responses'])
            ->where('ticket_code', $ticket_code)
            ->firstOrFail();

        $departments = HospitalDepartment::where('is_active', true)->orderBy('name')->get();

        return view('hospital.desk.show', compact('inquiry', 'departments'));
    }

    public function respond(Request $request, string $ticket_code): RedirectResponse
    {
        $inquiry = HospitalInquiry::where('ticket_code', $ticket_code)->firstOrFail();

        $validated = $request->validate([
            'response_text' => ['required', 'string', 'min:3', 'max:3000'],
            'author_name' => ['required', 'string', 'max:100'],
            'is_internal' => ['nullable', 'boolean'],
            'new_status' => ['nullable', 'string', 'in:open,in_progress,resolved,closed'],
            'department_id' => ['nullable', 'exists:hospital_departments,id'],
        ]);

        HospitalInquiryResponse::create([
            'inquiry_id' => $inquiry->id,
            'author_name' => $validated['author_name'],
            'response_text' => $validated['response_text'],
            'is_internal' => (bool) ($validated['is_internal'] ?? false),
        ]);

        $updates = [];

        if (!empty($validated['new_status'])) {
            $updates['status'] = $validated['new_status'];
            if ($validated['new_status'] === HospitalInquiry::STATUS_RESOLVED) {
                $updates['resolved_at'] = now();
            } elseif ($validated['new_status'] === HospitalInquiry::STATUS_OPEN) {
                $updates['resolved_at'] = null;
            }
        } elseif ($inquiry->status === HospitalInquiry::STATUS_OPEN && empty($validated['is_internal'])) {
            $updates['status'] = HospitalInquiry::STATUS_IN_PROGRESS;
        }

        if (isset($validated['department_id'])) {
            $updates['department_id'] = $validated['department_id'] ?: null;
        }

        if (!empty($updates)) {
            $inquiry->update($updates);
        }

        return redirect()
            ->route('hospital.desk.show', $inquiry->ticket_code)
            ->with('success', 'Response recorded successfully.');
    }

    public function updateStatus(Request $request, string $ticket_code): RedirectResponse
    {
        $inquiry = HospitalInquiry::where('ticket_code', $ticket_code)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,resolved,closed'],
            'staff_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $resolvedAt = $validated['status'] === HospitalInquiry::STATUS_RESOLVED ? now() : ($validated['status'] === HospitalInquiry::STATUS_OPEN ? null : $inquiry->resolved_at);

        $inquiry->update([
            'status' => $validated['status'],
            'staff_notes' => $validated['staff_notes'] ?? $inquiry->staff_notes,
            'resolved_at' => $resolvedAt,
        ]);

        return redirect()
            ->route('hospital.desk.show', $inquiry->ticket_code)
            ->with('success', 'Inquiry status updated.');
    }
}