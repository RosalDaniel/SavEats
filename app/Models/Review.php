<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'consumer_id',
        'food_listing_id',
        'establishment_id',
        'rating',
        'description',
        'image_path',
        'video_path',
        'flagged',
        'flagged_at',
    ];

    protected $casts = [
        'flagged' => 'boolean',
        'flagged_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function consumer()
    {
        return $this->belongsTo(Consumer::class, 'consumer_id', 'consumer_id');
    }

    public function foodListing()
    {
        return $this->belongsTo(FoodListing::class);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'establishment_id', 'establishment_id');
    }
}
