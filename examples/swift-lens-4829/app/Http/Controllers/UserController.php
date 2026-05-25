<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private const ACTIVE_DAYS = 30;

    public function index(Request $request)
    {
        $query = User::query();

        $query->when($request->filled('keyword'), function ($q) use ($request) {
            $kw = $request->keyword;
            $q->where(function ($inner) use ($kw) {
                $inner->where('username', 'like', "%{$kw}%")
                      ->orWhere('email', 'like', "%{$kw}%")
                      ->orWhere('city', 'like', "%{$kw}%")
                      ->orWhere('country', 'like', "%{$kw}%");
            });
        });

        $query->when($request->filled('country'), fn ($q) =>
            $q->where('country', $request->country)
        );

        $query->when($request->filled('city'), fn ($q) =>
            $q->where('city', $request->city)
        );

        $query->when($request->filled('device_type'), fn ($q) =>
            $q->where('device_type', $request->device_type)
        );

        $query->when($request->filled('signup_source'), fn ($q) =>
            $q->where('signup_source', $request->signup_source)
        );

        $query->when($request->has('has_avatar') && $request->has_avatar !== '', function ($q) use ($request) {
            if ($request->has_avatar == '1') {
                $q->whereNotNull('avatar');
            } else {
                $q->whereNull('avatar');
            }
        });

        $query->when($request->has('is_active') && $request->is_active !== '', function ($q) use ($request) {
            $threshold = now()->subDays(self::ACTIVE_DAYS);
            if ($request->is_active == '1') {
                $q->where('last_login_at', '>=', $threshold);
            } else {
                $q->where(fn ($inner) =>
                    $inner->where('last_login_at', '<', $threshold)
                          ->orWhereNull('last_login_at')
                );
            }
        });

        $period = $request->period;
        $query->when($period && $period !== 'custom', function ($q) use ($period) {
            $q->where('created_at', '>=', match ($period) {
                'day'   => now()->subDay(),
                'week'  => now()->subWeek(),
                'month' => now()->subMonth(),
                'year'  => now()->subYear(),
                default => now()->subYear(),
            });
        });

        $query->when($period === 'custom' && $request->filled('start_date') && $request->filled('end_date'), fn ($q) =>
            $q->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date   . ' 23:59:59',
            ])
        );

        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $countries = User::distinct()->orderBy('country')->pluck('country')->filter()->values();
        $cities    = User::distinct()->orderBy('city')->pluck('city')->filter()->values();

        return view('users.index', compact('users', 'countries', 'cities'));
    }
}
