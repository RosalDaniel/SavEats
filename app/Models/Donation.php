<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Donation extends Model
{
    use HasFactory;

    protected $table = 'donations';
    protected $primaryKey = 'donation_id';
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
            // Generate donation number if not set
            if (empty($model->donation_number)) {
                $model->donation_number = 'DON-' . strtoupper(Str::random(8));
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
        'establishment_id',
        'donation_request_id',
        'donation_number',
        'item_name',
        'item_category',
        'quantity',
        'unit',
        'description',
        'expiry_date',
        'status',
        'pickup_method',
        'scheduled_date',
        'scheduled_time',
        'collected_at',
        'handler_name',
        'establishment_notes',
        'foodbank_notes',
        'is_urgent',
        'is_nearing_expiry',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'expiry_date' => 'date',
            'scheduled_time' => 'datetime',
            'collected_at' => 'datetime',
            'is_urgent' => 'boolean',
            'is_nearing_expiry' => 'boolean',
            'quantity' => 'integer',
        ];
    }

    /**
     * Get the foodbank that received the donation.
     */
    public function foodbank(): BelongsTo
    {
        return $this->belongsTo(Foodbank::class, 'foodbank_id', 'foodbank_id');
    }

    /**
     * Get the establishment that made the donation.
     */
    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class, 'establishment_id', 'establishment_id');
    }

    /**
     * Get the donation request that this donation fulfills (if applicable).
     */
    public function donationRequest(): BelongsTo
    {
        return $this->belongsTo(DonationRequest::class, 'donation_request_id', 'donation_request_id');
    }
}
