<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class CommerceOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Commerce snapshot';

    protected ?string $description = 'Live counts from the storefront database.';

    protected function getStats(): array
    {
        $paidRevenueCents = (int) Order::query()
            ->where('status', 'paid')
            ->sum('amount_cents');

        $ordersToday = Order::query()
            ->whereDate('ordered_at', today())
            ->count();

        $pendingOrders = Order::query()
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('Paid revenue', Number::currency($paidRevenueCents / 100, 'USD'))
                ->description('All paid orders')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),
            Stat::make('Orders today', (string) $ordersToday)
                ->description($pendingOrders > 0 ? "{$pendingOrders} pending" : 'No pending orders')
                ->descriptionIcon(Heroicon::OutlinedShoppingBag)
                ->color($pendingOrders > 0 ? 'warning' : 'gray'),
            Stat::make('Customers', (string) Customer::query()->count())
                ->description('Registered buyers')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->color('primary'),
            Stat::make('Products', (string) Product::query()->count())
                ->description('Active catalog SKUs')
                ->descriptionIcon(Heroicon::OutlinedCube)
                ->color('info'),
        ];
    }
}
