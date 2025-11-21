<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodListing extends Model
{
    protected $fillable = [
        'establishment_id',
        'name',
        'description',
        'category',
        'quantity',
        'reserved_stock',
        'sold_stock',
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
        return $this->belongsTo(Establishment::class, 'establishment_id', 'establishment_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'food_listing_id');
    }

    public function stockLedgerEntries()
    {
        return $this->hasMany(StockLedger::class, 'food_listing_id');
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

    /**
     * Get the image URL with fallback for missing images
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path && \Storage::disk('public')->exists($this->image_path)) {
            return \Storage::url($this->image_path);
        }
        
        // Return a placeholder image if the file doesn't exist
        return 'https://via.placeholder.com/400x300/4a7c59/ffffff?text=' . strtoupper(substr($this->name, 0, 1));
    }

    /**
     * Clean up orphaned image references
     */
    public static function cleanupOrphanedImages()
    {
        $orphaned = self::whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->get()
            ->filter(function ($item) {
                return !\Storage::disk('public')->exists($item->image_path);
            });

        foreach ($orphaned as $item) {
            $item->update(['image_path' => null]);
        }

        return $orphaned->count();
    }

    /**
     * Get available stock (quantity - reserved_stock)
     */
    public function getAvailableStockAttribute()
    {
        return max(0, $this->quantity - ($this->reserved_stock ?? 0));
    }
}
