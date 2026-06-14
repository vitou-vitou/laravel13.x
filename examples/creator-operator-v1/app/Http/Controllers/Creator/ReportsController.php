<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $creator = $request->user()->creatorProfile;
        abort_if($creator === null, 403);

        $metrics = $creator->weeklyMetrics()->latest('week_start')->get();

        return view('creator.reports.index', compact('creator', 'metrics'));
    }
}
