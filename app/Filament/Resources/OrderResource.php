<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Pages\ViewOrder;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Order Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Section::make('Order Information')->schema([
                    TextInput::make('order_number')
                        ->required()
                        ->maxLength(255)
                        ->label('Order Number')
                        ->unique(ignoreRecord: true)
                        ->live(onBlur: true)
                        ->default('ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4))),
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select Customer')
                        ->label('Customer'),
                ])->columns(2),
                Section::make('Pricing')->schema([
                    TextInput::make('subtotal')
                        // ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp')
                        ->disabled(),
                    TextInput::make('discount_amount')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->prefix('Rp')
                        ->minValue(0),
                    TextInput::make('shipping_cost')
                        ->numeric()
                        ->default(0)
                        ->prefix('Rp')
                        ->minValue(0),
                    TextInput::make('total_amount')
                        // ->required()
                        ->numeric()
                        ->disabled()
                        ->prefix('Rp')
                        ->minValue(0),
                ])->columns(2),
                Section::make('Shipping and Status')->schema([
                    Textarea::make('shipping_address')
                        ->required()
                        // ->row(3)
                        ->maxLength(65535),
                    TextInput::make('phone_number')
                        ->required()
                        ->maxLength(255)
                        ->tel()
                        ->label('Phone (WhatsApp for Customer Order)'),
                    Textarea::make('notes')
                        ->nullable()
                        ->maxLength(65535)
                        ->label('Notes for Customer Order'),
                    Select::make('status')
                        ->options([
                            'PENDING' => 'Pending',
                            'PROCESSING' => 'Processing',
                            'SHIPPED' => 'Shipped',
                            'DELIVERED' => 'Delivered',
                            'CANCELLED' => 'Cancelled',
                        ])
                        ->default('PENDING')
                        ->required(),
                    DateTimePicker::make('paid_at')
                        ->nullable(),

                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('order_number')
                    ->label('Order Number')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->numeric()
                    ->money('IDR', true)
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->label('Discount Amount')
                    ->numeric()
                    ->money('IDR', true)
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('shipping_cost')
                    ->label('Shipping Cost')
                    ->numeric()
                    ->money('IDR', true)
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->numeric()
                    ->money('IDR', true)
                    ->sortable(),
                TextColumn::make('shipping_address')
                    ->label('Shipping Address')
                    ->searchable()
                    ->limit(50) // Limit to 50 characters for display
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    }),
                TextColumn::make('phone_number')
                    ->label('Phone (WhatsApp for Customer Order)')
                    ->searchable()
                    ->copyable()
                    ->limit(20), // Limit to 20 characters for display
                TextColumn::make('notes')
                    ->label('Notes for Customer Order')
                    ->searchable()
                    ->limit(50), // Limit to 50 characters for display
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PENDING' => 'warning',
                        'PROCESSING' => 'info',
                        'SHIPPED' => 'primary',
                        'DELIVERED' => 'success',
                        'CANCELLED' => 'danger',
                    })
                    ->label('Status'),
                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime()
                    ->placeholder('not paid')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
                SelectFilter::make('status')
                    ->options([
                        'PENDING' => 'Pending',
                        'PROCESSING' => 'Processing',
                        'SHIPPED' => 'Shipped',
                        'DELIVERED' => 'Delivered',
                        'CANCELLED' => 'Cancelled',
                    ])
                    ->label('Order Status'),
                SelectFilter::make('paid_status')
                    ->options([
                        'paid' => 'Paid',
                        'unpaid' => 'Unpaid',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'] === 'paid',
                                fn(Builder $query) => $query->whereNotNull('paid_at'),
                            )
                            ->when(
                                $data['value'] === 'unpaid',
                                fn(Builder $query) => $query->whereNull('paid_at'),
                            );
                    })
                    ->label('Payment Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
            RelationManagers\OrderItemRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ListOrders::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
