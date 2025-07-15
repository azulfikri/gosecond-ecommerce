<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Order;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrders extends BaseWidget
{
    protected static ?string $heading = 'Recent Orders';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full'; // Full width

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->with(['user'])->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'completed',
                    ])
                    ->icons([
                        'heroicon-m-x-circle' => 'cancelled',
                        'heroicon-m-clock' => 'pending',
                        'heroicon-m-cog' => 'processing',
                        'heroicon-m-check-circle' => 'completed',
                    ])
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn(Order $record): string => route('filament.admin.resources.orders.view', $record)),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
