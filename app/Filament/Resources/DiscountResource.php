<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Pages\ViewDiscount;
use App\Models\Discount;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\DiscountResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DiscountResource\RelationManagers;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Order Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('code')
                        ->nullable()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->default(fn() => 'THRIFT-' . strtoupper(Str::random(6))),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->nullable()
                        ->maxLength(65535),
                    Select::make('type')
                        ->options([
                            'PERCENTAGE' => 'Percentage',
                            'FIXED' => 'Fixed Amount',
                        ])
                        ->default('PERCENTAGE')
                        ->reactive()
                        ->afterStateUpdated(fn($state, callable $set) => $set('value', null))
                        ->label('Discount Type')
                        ->required(),
                    TextInput::make('value')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->label('Value (Percentage 1-100 or Fixed Amount in IDR)')
                        ->reactive()
                        ->rules(['required', 'integer', fn($get) => $get('type') === 'PERCENTAGE' ? 'max:100' : 'min:1'])
                        ->helperText(fn($get) => $get('type') === 'PERCENTAGE'
                            ? 'Enter percentage value (1-100)'
                            : 'Enter fixed amount in IDR'),
                    TextInput::make('minimum_purchase')
                        ->numeric()
                        ->nullable()
                        ->minValue(0)
                        ->label('Minimum Purchase (IDR)'),
                    TextInput::make('usage_limit')
                        ->numeric()
                        ->nullable()
                        ->minValue(1)
                        ->label('Max Usage (Optional)'),
                    TextInput::make('used_count')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->hiddenOn('create')
                        ->label('Used Count'),
                    Select::make('product_id')
                        ->relationship('product', 'name')
                        ->nullable()
                        ->preload()
                        ->searchable()
                        ->label('Specific Product (Optional)'),
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->nullable()
                        ->preload()
                        ->label('Specific Category (Optional)')
                        ->searchable(),
                    Toggle::make('is_active')
                        ->default(true)
                        ->label('Active'),
                    DateTimePicker::make('start_date')
                        ->required()
                        ->label('Start Date')
                        ->default(now()),
                    DateTimePicker::make('end_date')
                        ->required()
                        ->label('End Date')
                        ->afterOrEqual('start_date'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('code')->searchable()->copyable()->placeholder('No Code')->default('-'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('description')->limit(50)->tooltip(fn($record) => $record->description),
                TextColumn::make('type')->badge()
                    ->color(fn($state) => match ($state) {
                        'PERCENTAGE' => 'success',
                        'FIXED' => 'info',
                        default => 'gray'
                    }),
                TextColumn::make('value')
                    ->formatStateUsing(fn($state, $record) => $record->type === 'PERCENTAGE' ? "{$state}%" : number_format($state, 0, ',', '.') . ' IDR'),
                TextColumn::make('minimum_purchase')->money('IDR')->default('-'),
                TextColumn::make('usage_limit')->default('-'),
                TextColumn::make('used_count')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'PERCENTAGE' => 'success',
                        'FIXED' => 'info',
                        default => 'gray'
                    }),
                TextColumn::make('product.name')->placeholder('All Product')->default('-'),
                TextColumn::make('category.name')->placeholder('All Categories')
                    ->default('-'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('start_date')->dateTime()->sortable(),
                TextColumn::make('end_date')->dateTime()->sortable()->color(fn($state) => $state < now() ? 'danger' : 'success'),
                TextColumn::make('created_at')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                SelectFilter::make('type')
                    ->options([
                        'PERCENTAGE' => 'Percentage',
                        'FIXED' => 'Fixed Amount',
                    ]),
                TernaryFilter::make('is_active')->label('Status'),
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Product'),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Category'),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'view' => Pages\ListDiscounts::route('/{record}'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
