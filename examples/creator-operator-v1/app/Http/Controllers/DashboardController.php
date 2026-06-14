<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        return match ($user->role) {
            UserRole::Operator => redirect()->route('operator.dashboard'),
            UserRole::Creator => redirect()->route('creator.approvals.index'),
        };
    }
}
