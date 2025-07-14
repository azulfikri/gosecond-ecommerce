<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    /** @use HasFactory<\Database\Factories\CartItemFactory> */
    use HasFactory;
    protected $fillable = ['cart_id', 'product_id', 'size_id', 'quantity'];
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * Get subtotal for this cart item
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->product->price;
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 0, ',', '.') . ' IDR';
    }

    /**
     * Get formatted product price
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->product->price, 0, ',', '.') . ' IDR';
    }

    /**
     * Increment quantity
     */
    public function incrementQuantity($amount = 1)
    {
        $this->increment('quantity', $amount);
        return $this;
    }

    /**
     * Decrement quantity
     */
    public function decrementQuantity($amount = 1)
    {
        if ($this->quantity > $amount) {
            $this->decrement('quantity', $amount);
            return $this;
        } else {
            // Delete if quantity would be 0 or negative
            $this->delete();
            return null;
        }
    }

    /**
     * Set specific quantity
     */
    public function setQuantity($quantity)
    {
        if ($quantity <= 0) {
            $this->delete();
            return null;
        }

        $this->update(['quantity' => $quantity]);
        return $this;
    }

    /**
     * Get display name (product name + size if exists)
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name;

        if ($this->size) {
            $name .= ' - ' . $this->size->name;
        }

        return $name;
    }

    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('cart', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope untuk filter berdasarkan product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope untuk filter berdasarkan size
     */
    public function scopeForSize($query, $sizeId)
    {
        return $query->where('size_id', $sizeId);
    }
}
