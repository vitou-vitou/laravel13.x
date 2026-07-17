<?php

namespace App\Services;

use App\Mail\NewOrderMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class OrderMailNotifier
{
    public function notifyAdmins(Order $order): void
    {
        $order->loadMissing(['customer', 'items']);

        User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin'))
            ->get()
            ->each(function (User $admin) use ($order): void {
                Mail::to($admin)->queue(new NewOrderMail($order));
            });
    }
}
