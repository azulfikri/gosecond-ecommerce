<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProductImageRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image_path')
                    ->label('Product Image')
                    ->image()
                    ->required()
                    ->disk('public')
                    ->directory('product-images')
                    ->acceptedFileTypes(['image/jpg', 'image/jpeg', 'image/png', 'image/gif'])
                    ->maxSize(2048)
                    ->getUploadedFileNameForStorageUsing(function ($file) {
                        return 'product-' . time() . '-' . $file->getClientOriginalName();
                    }),
                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->nullable()
                    ->maxLength(255)
                    ->helperText('Provide alternative text for the image'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->label('Sort Order (lower number appears first)'),
                Toggle::make('is_primary')
                    ->label('Set as Primary Image')
                    ->default(false)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $livewire) {
                        if ($state) {
                            // Set all other images for this product to is_primary=false
                            $livewire->ownerRecord->images()
                                ->where('id', '!=', $livewire->record?->id ?? 0)
                                ->update(['is_primary' => false]);
                        }
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('alt_text') // Fixed: gunakan kolom yang ada
            ->columns([
                ImageColumn::make('image_path') // Fixed: langsung pakai image_path
                    ->label('Image')
                    ->disk('public')
                    ->width(80)
                    ->height(80),
                TextColumn::make('alt_text')
                    ->label('Alt Text')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('No alt text'), // Added placeholder for null values
                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),
                IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Primary Image')
                    ->placeholder('All images')
                    ->trueLabel('Primary only')
                    ->falseLabel('Non-primary only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Image'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order'); // Added: enable drag & drop reordering
    }
}
