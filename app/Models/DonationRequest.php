<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DonationRequest extends Model
{
    protected $table = 'donation_requests';
    protected $primaryKey = 'donation_request_id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'foodbank_id',
        'item_name',
        'quantity',
        'category',
        'description',
        'distribution_zone',
        'dropoff_date',
        'time_option',
        'start_time',
        'end_time',
        'address',
        'delivery_option',
        'contact_name',
        'phone_number',
        'email',
        'status',
        'matches',
        'fulfilled_by_establishment_id',
        'fulfilled_at',
        'donation_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dropoff_date' => 'date',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'quantity' => 'integer',
            'matches' => 'integer',
            'fulfilled_at' => 'datetime',
        ];
    }

    /**
     * Get the foodbank that owns the donation request.
     */
    public function foodbank(): BelongsTo
    {
        return $this->belongsTo(Foodbank::class, 'foodbank_id', 'foodbank_id');
    }

    /**
     * Get the establishment that fulfilled this request.
     */
    public function fulfilledBy(): BelongsTo
    {
        return $this->belongsTo(Establishment::class, 'fulfilled_by_establishment_id', 'establishment_id');
    }

    /**
     * Get the donation that fulfilled this request.
     */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donation_id');
    }

    /**
     * Scope a query to only include active requests.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'active']);
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
