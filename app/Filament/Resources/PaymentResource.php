<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Pages\ViewPayment;
use App\Models\Payment;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\PaymentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PaymentResource\RelationManagers;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Order Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Select::make('order_id')
                        ->relationship('order', 'id')
                        ->required(),
                    TextInput::make('amount')
                        ->required()
                        ->numeric(),
                    TextInput::make('fee_amount')
                        ->numeric()
                        ->nullable()
                        ->default(null),
                    TextInput::make('currency')
                        ->required()
                        ->maxLength(3)
                        ->default('IDR'),
                    TextInput::make('payment_method')
                        ->maxLength(255)
                        ->nullable()
                        ->default(null),
                    TextInput::make('transaction_id')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('status_message')
                        ->maxLength(255)
                        ->nullable()
                        ->default(null),
                    TextInput::make('payment_gateway')
                        ->maxLength(255)
                        ->nullable()
                        ->default(null),
                    Textarea::make('gateway_response')
                        ->nullable()
                        ->rows(6)
                        ->autosize(),
                    Select::make('status')->options([
                        'PENDING' => 'Pending',
                        'PROCESSING' => 'Processing',
                        'COMPLETED' => 'Completed',
                        'FAILED' => 'Failed',
                        'CANCELLED' => 'Cancelled',
                    ])->required(),
                    DateTimePicker::make('paid_at'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR', true)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fee_amount')
                    ->money('IDR', true)
                    ->default('-')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency')
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('transaction_id')
                    ->searchable(),
                TextColumn::make('status_message')
                    ->searchable(),
                TextColumn::make('payment_gateway')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('status')->badge()->colors([
                    'gray' => 'PENDING',
                    'warning' => 'PROCESSING',
                    'success' => 'COMPLETED',
                    'danger' => ['FAILED', 'CANCELLED'],
                ]),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPayments::route('/'),
            // 'view' => Pages\ListPayments::route('/{record}'),

            // 'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
