<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $tenant = auth()->user()->tenant()->with(['applications.telegramBot', 'applications.auditLogs' => fn ($q) => $q->latest()->limit(10)])->first();

        return view('dashboard.home', [
            'tenant' => $tenant,
        ]);
    }
}
