<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    //
    use HasFactory;
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function items()
    {
        return $this->hasMany(CartItem::class)->with('product');
    }

    /**
     * Get total items count in cart
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->cartItems->sum('quantity');
    }

    /**
     * Get total price of all items in cart
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->cartItems()->with('product')->get()->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return number_format($this->total_price, 0, ',', '.') . ' IDR';
    }

    /**
     * Add item to cart
     */
    public function addItem($productId, $quantity = 1, $sizeId = null)
    {
        $existingItem = $this->cartItems()
            ->where('product_id', $productId)
            ->where('size_id', $sizeId)
            ->first();

        if ($existingItem) {
            // Update quantity if item already exists
            $existingItem->increment('quantity', $quantity);
            return $existingItem;
        } else {
            // Create new cart item
            return $this->cartItems()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'size_id' => $sizeId,
            ]);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem($productId, $sizeId = null)
    {
        return $this->cartItems()
            ->where('product_id', $productId)
            ->where('size_id', $sizeId)
            ->delete();
    }

    /**
     * Update item quantity
     */
    public function updateItemQuantity($productId, $quantity, $sizeId = null)
    {
        if ($quantity <= 0) {
            return $this->removeItem($productId, $sizeId);
        }

        return $this->cartItems()
            ->where('product_id', $productId)
            ->where('size_id', $sizeId)
            ->update(['quantity' => $quantity]);
    }

    /**
     * Clear all items from cart
     */
    public function clearItems()
    {
        return $this->cartItems()->delete();
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return $this->cartItems()->count() === 0;
    }

    /**
     * Check if cart has specific product with size
     */
    public function hasProduct($productId, $sizeId = null): bool
    {
        return $this->cartItems()
            ->where('product_id', $productId)
            ->where('size_id', $sizeId)
            ->exists();
    }

    /**
     * Get cart for specific user (create if not exists)
     */
    public static function getForUser($userId)
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }
}
