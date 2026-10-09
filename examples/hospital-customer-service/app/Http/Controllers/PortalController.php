<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Faq;
use Illuminate\Contracts\View\View;

class PortalController extends Controller
{
    public function index(): View
    {
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        $faqTree = Faq::with('targetDepartment')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return view('portal', [
            'departments' => $departments,
            'faqTree' => $faqTree,
            'disclaimer' => 'This portal assists with non-clinical customer service, department navigation, and queue inquiries. In case of acute medical emergencies, call 911 or head directly to the Emergency Room.',
        ]);
    }
}
