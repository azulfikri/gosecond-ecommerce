<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    //
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_purchase',
        'usage_limit',
        'used_count',
        'product_id',
        'category_id',
        'is_active',
        'start_date',
        'end_date'
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($discount) {
            if ($discount->type === 'PERCENTAGE' && $discount->value > 100) {
                $discount->value = min($discount->value, 100);
            }
        });
    }

    /**
     * Get the usage status color for badge
     */
    public function getUsageStatusColorAttribute(): string
    {
        if (!$this->usage_limit) {
            return 'gray'; // Unlimited usage
        }

        $percentage = ($this->used_count / $this->usage_limit) * 100;

        return match (true) {
            $percentage >= 80 => 'danger',     // 80%+ = red
            $percentage >= 50 => 'warning',    // 50-79% = yellow
            default => 'success'               // <50% = green
        };
    }

    /**
     * Get the usage percentage
     */
    public function getUsagePercentageAttribute(): ?float
    {
        if (!$this->usage_limit) {
            return null;
        }

        return round(($this->used_count / $this->usage_limit) * 100, 1);
    }
}
