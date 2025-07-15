<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class RealTimeStats extends BaseWidget
{
    protected static ?string $pollingInterval = '5s'; // Update setiap 5 detik
    protected static ?int $sort = 8;
    protected function getStats(): array
    {
        return [
            //
            Stat::make('Online Users', User::where('updated_at', '>', now()->subMinutes(5))->count())
                ->description('Active in last 5 minutes')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Today Orders', Order::whereDate('created_at', today())->count())
                ->description('Orders today')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
        ];
    }
}
