<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Product;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProducts extends BaseWidget
{
    protected static ?string $heading = 'Top Selling Products';
    protected static ?int $sort = 5;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // ...
                Product::query()
                    ->withCount(['orderItems'])
                    ->orderBy('order_items_count', 'desc')
                    ->limit(5)
            )
            ->columns([
                // ...
                Tables\Columns\ImageColumn::make('image')
                    ->size(50),

                Tables\Columns\TextColumn::make('name')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('order_items_count')
                    ->label('Sold')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('stock')
                    ->badge()
                    ->color(fn(string $state): string => $state < 10 ? 'danger' : 'success'),
            ]);
    }
}
