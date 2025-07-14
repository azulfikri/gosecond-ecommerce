<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use App\Models\Size;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use App\Models\OrderItem;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class OrderItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    Select::make('product_id')
                        ->relationship('product', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select Product')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $get, $livewire) {
                            if (!$state) {
                                $set('product_name', null);
                                $set('unit_price', 0);
                                $set('total_price', 0);
                                $set('discount_id', null);
                                $set('discount_value', 0);
                                return;
                            }

                            $product = Product::find($state);
                            if ($product) {
                                if (!$product->price || $product->price <= 0) {
                                    $livewire->addError('product_id', 'Product price cannot be zero.');
                                    return;
                                }

                                $set('product_name', $product->name);
                                $set('unit_price', $product->price);
                                $quantity = $get('quantity') ?? 1;
                                $subtotal = $quantity * $product->price;
                                $set('total_price', $subtotal);

                                // Reset discount when product changes
                                $set('discount_id', null);
                                $set('discount_value', 0);
                            } else {
                                $livewire->addError('product_id', 'Product not found.');
                            }
                        }),

                    TextInput::make('product_name')
                        ->required()
                        ->disabled()
                        ->dehydrated(),

                    Select::make('size_id')
                        ->relationship('size', 'size_label')
                        ->nullable()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select Size')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $size = Size::find($state);
                            $set('size_name', $size ? $size->size_label : null);
                        }),

                    TextInput::make('size_name')
                        ->nullable()
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('quantity')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(1)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            $unitPrice = $get('unit_price') ?? 0;
                            $subtotal = $state * $unitPrice;

                            // Recalculate discount if exists
                            $discountId = $get('discount_id');
                            if ($discountId) {
                                $discount = \App\Models\Discount::find($discountId);
                                if ($discount) {
                                    $discountValue = $this->calculateDiscountValue($discount, $unitPrice, $state);
                                    $set('discount_value', $discountValue);
                                    $set('total_price', max(0, $subtotal - $discountValue));
                                } else {
                                    $set('discount_value', 0);
                                    $set('total_price', $subtotal);
                                }
                            } else {
                                $set('discount_value', 0);
                                $set('total_price', $subtotal);
                            }
                        }),

                    TextInput::make('unit_price')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->disabled()
                        ->dehydrated(),

                    TextInput::make('total_price')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->disabled()
                        ->dehydrated(),

                    Select::make('discount_id')
                        ->relationship('discount', 'code', fn($query) => $query->where('is_active', true))
                        ->nullable()
                        ->searchable()
                        ->preload()
                        ->placeholder('Select Discount (Optional)')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $get, $livewire) {
                            $unitPrice = $get('unit_price') ?? 0;
                            $quantity = $get('quantity') ?? 1;
                            $subtotal = $unitPrice * $quantity;

                            if ($state) {
                                $discount = \App\Models\Discount::find($state);
                                if ($discount) {
                                    // Check if discount is applicable
                                    if (!$this->isDiscountApplicable($discount, $subtotal)) {
                                        $livewire->addError('discount_id', 'Discount is not applicable for this order.');
                                        // Don't reset the field, just show error
                                        $set('discount_value', 0);
                                        $set('total_price', $subtotal);
                                        return;
                                    }

                                    $discountValue = $this->calculateDiscountValue($discount, $unitPrice, $quantity);
                                    $set('discount_value', $discountValue);
                                    $set('total_price', max(0, $subtotal - $discountValue));
                                } else {
                                    $set('discount_value', 0);
                                    $set('total_price', $subtotal);
                                }
                            } else {
                                $set('discount_value', 0);
                                $set('total_price', $subtotal);
                            }
                        }),

                    TextInput::make('discount_value')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->disabled()
                        ->dehydrated(),
                ])
            ]);
    }

    /**
     * Check if discount is applicable
     */
    private function isDiscountApplicable($discount, $subtotal): bool
    {
        if (!$discount || !$discount->is_active) {
            return false;
        }

        // Check dates
        $now = now();
        if ($discount->start_date && $now->lt($discount->start_date)) {
            return false;
        }
        if ($discount->end_date && $now->gt($discount->end_date)) {
            return false;
        }

        // Check minimum purchase
        if ($discount->minimum_purchase && $subtotal < $discount->minimum_purchase) {
            return false;
        }

        // Check usage limit
        if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount value
     */
    private function calculateDiscountValue($discount, $unitPrice, $quantity): float
    {
        $subtotal = $unitPrice * $quantity;

        if ($discount->type === 'PERCENTAGE') {
            return $subtotal * ($discount->value / 100);
        }

        if ($discount->type === 'FIXED') {
            return min($discount->value, $subtotal);
        }

        return 0;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('size_name')
                    ->label('Size')
                    ->default('-'),
                TextColumn::make('quantity')->sortable(),
                TextColumn::make('unit_price')->money('IDR')->sortable(),
                TextColumn::make('total_price')->money('IDR')->sortable()->weight('bold'),
                TextColumn::make('discount.code')
                    ->label('Discount')
                    ->default('-')
                    ->badge()
                    ->color('success'),
                TextColumn::make('discount_value')->money('IDR')->default(0)->color('danger'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Item')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['quantity'])) {
                            $data['quantity'] = 1;
                        }
                        if (empty($data['unit_price'])) {
                            $product = Product::find($data['product_id']);
                            $data['unit_price'] = $product ? $product->price : 0;
                        }
                        // Ensure discount_value and total_price are set
                        $subtotal = $data['quantity'] * $data['unit_price'];
                        if (!empty($data['discount_id'])) {
                            $discount = \App\Models\Discount::find($data['discount_id']);
                            if ($discount) {
                                $orderItem = new OrderItem([
                                    'product_id' => $data['product_id'],
                                    'unit_price' => $data['unit_price'],
                                    'quantity' => $data['quantity'],
                                ]);
                                if ($orderItem->isDiscountApplicable($discount, $subtotal)) {
                                    $data['discount_value'] = $discount->type === 'PERCENTAGE'
                                        ? ($subtotal * $discount->value / 100)
                                        : min($discount->value, $subtotal);
                                } else {
                                    Log::warning('Discount not applicable on create', [
                                        'discount_id' => $data['discount_id'],
                                        'subtotal' => $subtotal,
                                        'product_id' => $data['product_id'],
                                    ]);
                                    $data['discount_id'] = null;
                                    $data['discount_value'] = 0;
                                }
                            } else {
                                $data['discount_value'] = 0;
                            }
                        } else {
                            $data['discount_value'] = 0;
                        }
                        $data['total_price'] = max(0, $subtotal - ($data['discount_value'] ?? 0));
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['unit_price'])) {
                            $product = Product::find($data['product_id']);
                            $data['unit_price'] = $product ? $product->price : 0;
                        }
                        // Ensure discount_value and total_price are set
                        $subtotal = $data['quantity'] * $data['unit_price'];
                        if (!empty($data['discount_id'])) {
                            $discount = \App\Models\Discount::find($data['discount_id']);
                            if ($discount) {
                                $orderItem = new OrderItem([
                                    'product_id' => $data['product_id'],
                                    'unit_price' => $data['unit_price'],
                                    'quantity' => $data['quantity'],
                                ]);
                                if ($orderItem->isDiscountApplicable($discount, $subtotal)) {
                                    $data['discount_value'] = $discount->type === 'PERCENTAGE'
                                        ? ($subtotal * $discount->value / 100)
                                        : min($discount->value, $subtotal);
                                } else {
                                    Log::warning('Discount not applicable on edit', [
                                        'discount_id' => $data['discount_id'],
                                        'subtotal' => $subtotal,
                                        'product_id' => $data['product_id'],
                                    ]);
                                    $data['discount_id'] = null;
                                    $data['discount_value'] = 0;
                                }
                            } else {
                                $data['discount_value'] = 0;
                            }
                        } else {
                            $data['discount_value'] = 0;
                        }
                        $data['total_price'] = max(0, $subtotal - ($data['discount_value'] ?? 0));
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
