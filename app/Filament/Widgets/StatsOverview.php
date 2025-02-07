<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Created tickets', '10,000')
                ->description('0% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('gray'),
            Stat::make('Unsolved tickets', '10,000')
                ->description('0% decrease')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('gray'),
            Stat::make('Solved tickets', '10,000')
                ->description('0% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('gray'),
        ];
    }
}
