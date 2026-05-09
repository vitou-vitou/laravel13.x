<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Placeholder until the demo-item resource exists; replaced in a later commit.
 */
class DemoItemCountWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Your demo items', '0')
                ->description('Dashboard widget — full stats after resource is added.'),
        ];
    }
}
