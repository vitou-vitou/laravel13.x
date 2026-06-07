<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogBoard;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(ActivityLogBoard $activityLogBoard): View
    {
        return view('welcome', [
            'supabaseHealth' => null,
            'activityLogs' => $activityLogBoard->groupedSnapshot(ActivityLogBoard::DEFAULT_LIMIT),
            'logBoard' => $activityLogBoard,
        ]);
    }
}
