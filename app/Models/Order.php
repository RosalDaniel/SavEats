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
        'status',
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
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function consumer()
    {
        return $this->belongsTo(Consumer::class);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }

    public function foodListing()
    {
        return $this->belongsTo(FoodListing::class);
    }

    // Generate unique order number
    public static function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
