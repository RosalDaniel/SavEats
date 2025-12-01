<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Services\DonationRequestService;

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
        'establishment_id',
        'item_name',
        'quantity',
        'unit',
        'category',
        'description',
        'expiry_date',
        'scheduled_date',
        'scheduled_time',
        'pickup_method',
        'establishment_notes',
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
            'expiry_date' => 'date',
            'scheduled_date' => 'date',
            'scheduled_time' => 'datetime',
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
     * Get the establishment that created this donation request.
     */
    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class, 'establishment_id', 'establishment_id');
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
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', DonationRequestService::STATUS_PENDING);
    }

    /**
     * Scope a query to only include accepted requests.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', DonationRequestService::STATUS_ACCEPTED);
    }

    /**
     * Scope a query to only include completed requests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', DonationRequestService::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include declined requests.
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', DonationRequestService::STATUS_DECLINED);
    }

    /**
     * Check if request can be accepted
     */
    public function canBeAccepted(): bool
    {
        return $this->status === DonationRequestService::STATUS_PENDING;
    }

    /**
     * Check if request can be declined
     */
    public function canBeDeclined(): bool
    {
        return $this->status === DonationRequestService::STATUS_PENDING;
    }

    /**
     * Check if request can be completed
     */
    public function canBeCompleted(): bool
    {
        return in_array($this->status, [
            DonationRequestService::STATUS_ACCEPTED,
            DonationRequestService::STATUS_PENDING_CONFIRMATION
        ]);
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return DonationRequestService::getStatusDisplay($this->status);
    }
}
