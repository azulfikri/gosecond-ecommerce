<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //
    protected $table = 'order_items';
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'size_id',
        'size_name',
        'quantity',
        'unit_price',
        'total_price',
        'discount_id',
        'discount_value',
    ];
    protected $casts = [
        'quantity' => 'integer',
        // 'unit_price' => 'decimal:2',
        // 'total_price' => 'decimal:2',
        // 'discount_value' => 'decimal:2',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($item) {
            if (!$item->product_name && $item->product) {
                $item->product_name = $item->product->name;
            }
            if (!$item->size_name && $item->size) {
                $item->size_name = $item->size->size_label;
            }
            if (!$item->unit_price && $item->product) {
                $item->unit_price = $item->product->price;
            }
            if (!$item->unit_price) {
                throw new \Exception('Unit price cannot be zero for product ID: ' . $item->product_id);
            }
            $item->calculatePrices();
            if ($item->discount_id && $item->discount) {
                $item->discount->increment('used_count');
            }
        });
        static::updating(function ($item) {
            if (!$item->product_name && $item->product) {
                $item->product_name = $item->product->name;
            }
            if (!$item->size_name && $item->size) {
                $item->size_name = $item->size->size_label;
            }
            if (!$item->unit_price && $item->product) {
                $item->unit_price = $item->product->price;
            }
            if (!$item->unit_price) {
                throw new \Exception('Unit price cannot be zero for product ID: ' . $item->product_id);
            }
            if ($item->isDirty(['quantity', 'unit_price', 'discount_id'])) {
                $item->calculatePrices();
            }
            if ($item->isDirty('discount_id')) {
                if ($item->getOriginal('discount_id')) {
                    $oldDiscount = Discount::find($item->getOriginal('discount_id'));
                    if ($oldDiscount) {
                        $oldDiscount->decrement('used_count');
                    }
                }
                if ($item->discount_id && $item->discount) {
                    $item->discount->increment('used_count');
                }
            }
        });
        static::deleting(function ($item) {
            if ($item->discount_id && $item->discount) {
                $item->discount->decrement('used_count');
            }
        });
    }

    public function calculatePrices()
    {
        $subtotal = $this->unit_price * $this->quantity;
        $discountValue = 0;
        if ($this->discount_id && $this->discount) {
            $discount = $this->discount;
            if ($this->isDiscountApplicable($discount, $subtotal)) {
                $discountValue = $discount->type === 'PERCENTAGE'
                    ? ($subtotal * $discount->value / 100)
                    : min($discount->value, $subtotal);
            }
        }
        $this->discount_value = $discountValue;
        $this->total_price = max(0, $subtotal - $discountValue);
    }
    public function isDiscountApplicable($discount, $subtotal): bool
    {
        if (!$this->product_id && $discount->product_id) {
            Log::warning('Discount not applicable: No product_id', ['discount_id' => $discount->id]);
            return false;
        }
        if (!$discount->is_active) {
            Log::warning('Discount not applicable: Not active', ['discount_id' => $discount->id]);
            return false;
        }
        $now = now();
        if ($discount->start_date && $discount->start_date > $now) {
            Log::warning('Discount not applicable: Start date in future', [
                'discount_id' => $discount->id,
                'start_date' => $discount->start_date,
            ]);
            return false;
        }
        if ($discount->end_date && $discount->end_date < $now) {
            Log::warning('Discount not applicable: Expired', [
                'discount_id' => $discount->id,
                'end_date' => $discount->end_date,
            ]);
            return false;
        }
        if ($discount->minimum_purchase && $subtotal < $discount->minimum_purchase) {
            Log::warning('Discount not applicable: Subtotal below minimum', [
                'discount_id' => $discount->id,
                'subtotal' => $subtotal,
                'minimum_purchase' => $discount->minimum_purchase,
            ]);
            return false;
        }
        if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
            Log::warning('Discount not applicable: Usage limit reached', [
                'discount_id' => $discount->id,
                'used_count' => $discount->used_count,
                'usage_limit' => $discount->usage_limit,
            ]);
            return false;
        }
        if ($discount->product_id && $discount->product_id != $this->product_id) {
            Log::warning('Discount not applicable: Product mismatch', [
                'discount_id' => $discount->id,
                'product_id' => $this->product_id,
                'discount_product_id' => $discount->product_id,
            ]);
            return false;
        }
        if ($discount->category_id && $this->product && $this->product->category_id != $discount->category_id) {
            Log::warning('Discount not applicable: Category mismatch', [
                'discount_id' => $discount->id,
                'product_category_id' => $this->product ? $this->product->category_id : null,
                'discount_category_id' => $discount->category_id,
            ]);
            return false;
        }
        return true;
    }


    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function getFormattedDiscountValueAttribute(): string
    {
        return 'Rp ' . number_format($this->discount_value, 0, ',', '.');
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }
}
