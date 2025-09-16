<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodListing extends Model
{
    protected $fillable = [
        'establishment_id',
        'name',
        'description',
        'category',
        'quantity',
        'original_price',
        'discount_percentage',
        'discounted_price',
        'expiry_date',
        'address',
        'pickup_available',
        'delivery_available',
        'image_path',
        'status'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'original_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'pickup_available' => 'boolean',
        'delivery_available' => 'boolean',
    ];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>=', now()->toDateString());
    }

    public function getIsExpiredAttribute()
    {
        return $this->expiry_date < now()->toDateString();
    }

    public function getFormattedPriceAttribute()
    {
        return '₱' . number_format($this->original_price, 2);
    }

    public function getFormattedDiscountedPriceAttribute()
    {
        return $this->discounted_price ? '₱' . number_format($this->discounted_price, 2) : null;
    }
}
