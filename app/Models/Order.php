<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'consumer_id',
        'establishment_id',
        'food_listing_id',
        'quantity',
        'unit_price',
        'total_price',
        'delivery_method',
        'payment_method',
        'payment_status',
        'payment_confirmed_at',
        'status',
        'stock_deducted',
        'stock_deducted_at',
        'stock_restored',
        'stock_restored_at',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'pickup_start_time',
        'pickup_end_time',
        'accepted_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'stock_deducted' => 'boolean',
        'stock_restored' => 'boolean',
        'payment_confirmed_at' => 'datetime',
        'stock_deducted_at' => 'datetime',
        'stock_restored_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function consumer()
    {
        return $this->belongsTo(Consumer::class, 'consumer_id', 'consumer_id');
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'establishment_id', 'establishment_id');
    }

    public function foodListing()
    {
        return $this->belongsTo(FoodListing::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function stockLedgerEntries()
    {
        return $this->hasMany(StockLedger::class);
    }

    // Generate unique order number
    public static function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Check if pickup time has passed (missed pickup)
     */
    public function isMissedPickup()
    {
        // Only check for pickup orders that are accepted or pending
        if ($this->delivery_method !== 'pickup') {
            return false;
        }

        // Only check if order is not completed or cancelled
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }

        // Check if pickup_end_time exists and has passed
        if (!$this->pickup_end_time) {
            return false;
        }

        // Use the order's created_at date as the pickup date
        // pickup_end_time is stored as TIME, handle both string and object formats
        $pickupDate = $this->created_at ? $this->created_at->toDateString() : now()->toDateString();
        $pickupEndTimeStr = is_string($this->pickup_end_time) 
            ? $this->pickup_end_time 
            : $this->pickup_end_time->format('H:i:s');
        $pickupEndDateTime = $pickupDate . ' ' . $pickupEndTimeStr;
        $pickupEnd = \Carbon\Carbon::parse($pickupEndDateTime);

        // If pickup_end_time is in the past, it's missed
        return now()->greaterThan($pickupEnd);
    }

    /**
     * Get the effective status (including missed pickup)
     */
    public function getEffectiveStatusAttribute()
    {
        if ($this->isMissedPickup()) {
            return 'missed_pickup';
        }
        return $this->status;
    }
}
