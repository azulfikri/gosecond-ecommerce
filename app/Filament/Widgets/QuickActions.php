<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class QuickActions extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';
    protected static ?int $sort = 6;
    // protected int | string | array $columnSpan = 'full';

    public $todayOrders;
    public $pendingOrders;
    public $lowStock;
    public $newUsers;

    public function mount()
    {
        $this->todayOrders = Order::whereDate('created_at', today())->count();
        $this->pendingOrders = Order::where('status', 'pending')->count();
        $this->lowStock = Product::where('stock', '<', 10)->count();
        $this->newUsers = User::whereDate('created_at', today())->count();
    }
}
