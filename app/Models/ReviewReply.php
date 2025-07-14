<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewReply extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewReplyFactory> */
    use HasFactory;
    protected $fillable = ['product_review_id', 'user_id', 'reply'];
    public function review()
    {
        return $this->belongsTo(ProductReview::class, 'product_review_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeLatestReplies($query, $limit = 5)
    {
        return $query->latest()->take($limit);
    }

    /**
     * Check if this reply is from the product owner/seller
     */
    public function isFromSeller()
    {
        // Asumsi bahwa seller adalah user yang memiliki product
        return $this->user_id === $this->productReview->product->user_id;
    }

    /**
     * Check if this reply is from the original reviewer
     */
    public function isFromReviewer()
    {
        return $this->user_id === $this->productReview->user_id;
    }

    /**
     * Scope to get replies from seller only
     */
    public function scopeFromSeller($query)
    {
        return $query->whereHas('productReview.product', function ($q) {
            $q->whereColumn('products.user_id', 'review_replies.user_id');
        });
    }

    /**
     * Scope to get replies from reviewer only
     */
    public function scopeFromReviewer($query)
    {
        return $query->whereHas('productReview', function ($q) {
            $q->whereColumn('product_reviews.user_id', 'review_replies.user_id');
        });
    }
}
