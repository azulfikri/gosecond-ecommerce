<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BrandProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'Products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Nama Produk')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('slug', Str::slug($state)); // Auto-generate slug from name
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->unique(table: Product::class, ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('description')
                        ->nullable()
                        ->maxLength(500)
                        ->label('Deskripsi Produk'),
                    TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->money('IDR', true)
                        ->label('Harga Produk')
                        ->minValue(0),
                    TextInput::make('stock')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->maxLength(11)
                        ->label('Stok Produk'),
                    TextInput::make('weight')
                        ->nullable()
                        ->numeric()
                        ->maxLength(11)
                        ->minValue(0)
                        ->suffix('gram')
                        ->label('Berat Produk (gram)'),
                    Select::make('condition')
                        ->options([
                            'new' => 'Baru',
                            'used' => 'Second',
                        ])
                        ->default('used')
                        ->required()
                        ->label('Kondisi Produk'),
                    Select::make('brand_id')
                        ->label('Brand Produk')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->required()
                        ->default(fn($livewire) => $livewire->ownerRecord->id) // Auto-set ke brand saat ini
                        ->disabled(), // Nonaktifkan agar nggak bisa ubah brand
                    Select::make('category_id')
                        ->label('Kategori Produk')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->required(),
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->sortable()
                    ->label('Deskripsi Produk'),
                TextColumn::make('price')
                    ->label('Harga Produk')
                    ->prefix('Rp')
                    ->money('IDR', true)
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stok Produk')
                    ->sortable()
                    ->default(0),
                TextColumn::make('weight')
                    ->label('Berat Produk (gram)')
                    ->suffix('gram')
                    ->sortable()
                    ->default(0),
                TextColumn::make('condition')
                    ->label('Kondisi Produk'),
                TextColumn::make('brand.name')
                    ->label('Merek Produk')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori Produk')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->dateTime()
            ])
            ->filters([
                //
                SelectFilter::make('condition')
                    ->options([
                        'new' => 'Baru',
                        'used' => 'Bekas',
                    ])
                    ->label('Kondisi Produk'),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori Produk'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
