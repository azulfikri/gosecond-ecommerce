<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Revenue';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $revenue = Order::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('status', 'completed')
                ->sum('total_amount');
            $data[] = $revenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (Rp)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bubble';
    }
}
