<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model
{
    use HasFactory;

    protected $table = 'stock_ledger';

    protected $fillable = [
        'food_listing_id',
        'order_id',
        'transaction_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reserved_stock_before',
        'reserved_stock_after',
        'sold_stock_before',
        'sold_stock_after',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'reserved_stock_before' => 'integer',
        'reserved_stock_after' => 'integer',
        'sold_stock_before' => 'integer',
        'sold_stock_after' => 'integer',
    ];

    /**
     * Get the food listing that owns this ledger entry.
     */
    public function foodListing(): BelongsTo
    {
        return $this->belongsTo(FoodListing::class);
    }

    /**
     * Get the order associated with this ledger entry.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
