<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class FilterableStats extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';


    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? Carbon::now()->subDays(30);
        $endDate = $this->filters['endDate'] ?? Carbon::now();

        $orders = Order::whereBetween('created_at', [$startDate, $endDate]);

        return [
            Stat::make('Orders in Period', $orders->count())
                ->description('From ' . Carbon::parse($startDate)->format('M d') . ' to ' . Carbon::parse($endDate)->format('M d'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Revenue in Period', 'Rp ' . number_format($orders->where('status', 'completed')->sum('total_amount')))
                ->description('Completed orders only')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
