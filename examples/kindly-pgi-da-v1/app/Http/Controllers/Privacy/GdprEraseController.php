<?php

namespace App\Http\Controllers\Privacy;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GdprEraseController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        DB::transaction(function () use ($user) {
            $user->orders()
                ->where('status', OrderStatus::PendingPayment)
                ->each(fn ($order) => $order->update(['status' => OrderStatus::Cancelled]));

            $user->update([
                'name' => 'Deleted user',
                'email' => 'deleted-'.$user->id.'@erased.local',
                'password' => bcrypt(Str::random(32)),
            ]);
        });

        auth()->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account has been anonymized.');
    }
}
