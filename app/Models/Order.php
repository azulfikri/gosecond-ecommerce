<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = [
        'order_number',
        'user_id',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'total_amount',
        'shipping_address',
        'phone_number',
        'notes',
        'status',
        'paid_at',
    ];
    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
            }
        });
        static::saving(function ($order) {
            $order->subtotal = $order->items->sum('total_price');
            $order->discount_amount = $order->items->sum('discount_value');
            $order->total_amount = $order->subtotal + $order->shipping_cost;
            if ($order->subtotal < 0 || $order->total_amount < 0) {
                throw new \Exception('Order subtotal or total amount cannot be negative.');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // public function payment()
    // {
    //     return $this->hasOne(Payment::class);
    // }

}
