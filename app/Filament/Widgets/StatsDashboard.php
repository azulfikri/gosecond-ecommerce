<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsDashboard extends BaseWidget
{
    protected static ?int $sort = 1;
    // protected static ?string $heading = 'Dashboard Stats';
    protected function getStats(): array
    {
        return [
            //
            Stat::make('Total Users', User::count())
                ->description('Total registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Total Products', Product::count())
                ->description('Products in catalog')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
            Stat::make('Total Category', Category::count())
                ->description('Category in catalog')
                ->descriptionIcon('heroicon-o-tag')
                ->color('dark'),
            Stat::make('Total Brand', Brand::count())
                ->description('Brand in catalog')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('dark'),

            Stat::make('Total Orders', Order::count())
                ->description('All time orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),


            Stat::make('Total Revenue', 'Rp ' . number_format(Order::where('status', 'completed')->sum('total_amount')))
                ->description('Completed orders only')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Avg Rating', number_format(ProductReview::avg('rating'), 1) . '/5')
                ->description('Product ratings')
                ->descriptionIcon('heroicon-m-star')
                ->color('danger'),
        ];
    }
}
