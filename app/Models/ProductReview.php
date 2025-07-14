<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    /** @use HasFactory<\Database\Factories\ProductReviewFactory> */
    use HasFactory;
    protected $fillable = ['user_id', 'product_id', 'order_id', 'rating', 'comment', 'is_approved'];
    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function replies()
    {
        return $this->hasMany(ReviewReply::class);
    }
    /**
     * Scope to filter approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
    /**
     * Scope to get reviews by rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope to get reviews with minimum rating
     */
    public function scopeMinRating($query, $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

    /**
     * Get rating in stars format
     */
    public function getRatingStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Check if review can be replied to
     */
    public function canBeRepliedTo()
    {
        return $this->is_approved;
    }
}
