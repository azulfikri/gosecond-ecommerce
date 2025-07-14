<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    //
    protected $table = 'product_images';
    protected $fillable = [
        'product_id',
        'image_path',
        'alt_text',
        'sort_order',
        'is_primary',
    ];
    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(
            function ($model) {
                if (!$model->sort_order) {
                    $maxOrder = static::where('product_id', $model->product_id)->max('sort_order');
                    $model->sort_order = ($maxOrder ?? 0) + 1;
                }
            }
        );
    }
}
