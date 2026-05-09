<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\DemoItems\DemoItemResource;
use App\Models\DemoItem;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DemoItemCountWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $count = DemoItem::query()
            ->where('guest_id', Filament::auth()->id())
            ->count();

        return [
            Stat::make('Your demo items', (string) $count)
                ->description('Registration not implemented in this demo — link your own user flow in production.')
                ->url(DemoItemResource::getUrl('index')),
        ];
    }
}
