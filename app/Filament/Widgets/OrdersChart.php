<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Orders Per Day (Last 7 Days)';
    protected static ?int $sort = 2;
    protected static string $color = 'info';
    protected static ?string $pollingInterval = '10s'; // Auto refresh setiap 10 detik


    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = Order::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
