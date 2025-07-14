<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Product Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Section::make()->schema([
                    TextInput::make('name')->required()
                        ->label('Nama Produk')
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('slug', Str::slug($state)); // Auto-generate slug from name
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('description')
                        ->nullable()
                        ->maxLength(500)
                        ->label('Deskripsi Produk'),
                    TextInput::make('price')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        // ->maxLength(11)
                        ->label('Harga Produk'),
                    TextInput::make('stock')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->maxLength(11)
                        ->label('Stok Produk'),
                    TextInput::make('weight')
                        ->required()
                        ->numeric()
                        ->suffix('gram')
                        ->default(0)
                        ->maxLength(11)
                        ->label('Berat Produk (gram)'),
                    Select::make('condition')
                        ->options([
                            'new' => 'Baru',
                            'used' => 'Bekas',
                        ])
                        ->default('used')
                        ->label('Kondisi Produk'),
                    Select::make('brand_id')
                        ->relationship('brand', 'name')
                        ->required()
                        ->label('Merek Produk'),
                    Select::make('category_id')
                        ->relationship('category', 'name')
                        ->required()
                        ->label('Kategori Produk'),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                ImageColumn::make('image_path')
                    ->label('Gambar Utama')
                    ->getStateUsing(fn($record) => $record->images()->where('is_primary', true)->first()?->image_path ?? $record->images()->orderBy('sort_order')->first()?->image_path)
                    ->disk('public')
                    ->width(80)
                    ->height(80),
                TextColumn::make('description')
                    ->limit(50)
                    ->sortable()
                    ->label('Deskripsi Produk'),
                TextColumn::make('price')
                    ->label('Harga Produk')
                    ->money('IDR', true)
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Stok Produk')
                    ->sortable(),
                TextColumn::make('weight')
                    ->label('Berat Produk (gram)')
                    ->suffix('gram')
                    ->sortable(),
                TextColumn::make('condition')
                    ->label('Kondisi Produk'),
                TextColumn::make('brand.name')
                    ->label('Merek Produk')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori Produk')
                    ->sortable(),
                TextColumn::make('sizes.size_label')
                    ->label('Sizes')
                    ->formatStateUsing(function ($record) {
                        // $record adalah model Product
                        return $record->sizes->pluck('size_label')->implode(' | ');
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->filters([
                //
                SelectFilter::make('condition')
                    ->options([
                        'new' => 'Baru',
                        'used' => 'Bekas',
                    ])
                    ->label('Kondisi Produk'),
                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->label('Merek Produk'),
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Kategori Produk'),
                SelectFilter::make('sizes')
                    ->relationship('sizes', 'size_label')
                    ->multiple(),
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

    public static function getRelations(): array
    {
        return [
            //
            RelationManagers\SizeRelationManager::class,
            RelationManagers\ProductImageRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
