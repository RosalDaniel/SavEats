<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'severity',
        'user_type',
        'user_id',
        'user_email',
        'ip_address',
        'user_agent',
        'action',
        'description',
        'metadata',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to filter by event type.
     */
    public function scopeEventType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope a query to filter by severity.
     */
    public function scopeSeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope a query to filter by user type.
     */
    public function scopeUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by foodbank ID (from metadata).
     */
    public function scopeFoodbankId($query, $foodbankId)
    {
        return $query->where(function($q) use ($foodbankId) {
            // Support both PostgreSQL and MySQL JSON queries
            if (config('database.default') === 'pgsql') {
                $q->whereRaw("metadata->>'foodbank_id' = ?", [$foodbankId])
                  ->orWhereRaw("metadata->>'foodbank_id'::text = ?", [$foodbankId]);
            } else {
                $q->whereJsonContains('metadata->foodbank_id', $foodbankId)
                  ->orWhere('metadata', 'like', "%\"foodbank_id\":\"{$foodbankId}\"%");
            }
        });
    }

    /**
     * Scope a query to filter by establishment ID (from metadata).
     */
    public function scopeEstablishmentId($query, $establishmentId)
    {
        return $query->where(function($q) use ($establishmentId) {
            if (config('database.default') === 'pgsql') {
                $q->whereRaw("metadata->>'establishment_id' = ?", [$establishmentId])
                  ->orWhereRaw("metadata->>'establishment_id'::text = ?", [$establishmentId]);
            } else {
                $q->whereJsonContains('metadata->establishment_id', $establishmentId)
                  ->orWhere('metadata', 'like', "%\"establishment_id\":\"{$establishmentId}\"%");
            }
        });
    }

    /**
     * Scope a query to filter by donation ID (from metadata).
     */
    public function scopeDonationId($query, $donationId)
    {
        return $query->where(function($q) use ($donationId) {
            if (config('database.default') === 'pgsql') {
                $q->whereRaw("metadata->>'donation_id' = ?", [$donationId])
                  ->orWhereRaw("metadata->>'donation_id'::text = ?", [$donationId])
                  ->orWhere('description', 'like', "%{$donationId}%");
            } else {
                $q->whereJsonContains('metadata->donation_id', $donationId)
                  ->orWhere('metadata', 'like', "%\"donation_id\":\"{$donationId}\"%")
                  ->orWhere('description', 'like', "%{$donationId}%");
            }
        });
    }

    /**
     * Scope a query to filter by donation request ID (from metadata).
     */
    public function scopeDonationRequestId($query, $requestId)
    {
        return $query->where(function($q) use ($requestId) {
            if (config('database.default') === 'pgsql') {
                $q->whereRaw("metadata->>'donation_request_id' = ?", [$requestId])
                  ->orWhereRaw("metadata->>'donation_request_id'::text = ?", [$requestId])
                  ->orWhere('description', 'like', "%{$requestId}%");
            } else {
                $q->whereJsonContains('metadata->donation_request_id', $requestId)
                  ->orWhere('metadata', 'like', "%\"donation_request_id\":\"{$requestId}\"%")
                  ->orWhere('description', 'like', "%{$requestId}%");
            }
        });
    }

    /**
     * Scope a query to filter donation-related events.
     */
    public function scopeDonationEvents($query)
    {
        return $query->whereIn('event_type', ['donation', 'donation_request'])
                    ->orWhere('action', 'like', 'donation%');
    }

    /**
     * Get foodbank name from metadata.
     */
    public function getFoodbankNameAttribute()
    {
        return $this->metadata['foodbank_name'] ?? null;
    }

    /**
     * Get establishment name from metadata.
     */
    public function getEstablishmentNameAttribute()
    {
        return $this->metadata['establishment_name'] ?? null;
    }

    /**
     * Get donation ID from metadata.
     */
    public function getDonationIdAttribute()
    {
        return $this->metadata['donation_id'] ?? null;
    }

    /**
     * Get donation request ID from metadata.
     */
    public function getDonationRequestIdAttribute()
    {
        return $this->metadata['donation_request_id'] ?? null;
    }

    /**
     * Get the severity badge color.
     */
    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'critical' => 'red',
            'error' => 'orange',
            'warning' => 'yellow',
            'info' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'success' => 'green',
            'failed' => 'red',
            'blocked' => 'dark-red',
            default => 'gray',
        };
    }

    /**
     * Static method to log an event.
     */
    public static function log($eventType, $action, $description = null, $severity = 'info', $status = 'success', $metadata = [])
    {
        $request = request();
        
        return self::create([
            'event_type' => $eventType,
            'severity' => $severity,
            'user_type' => session('user_type'),
            'user_id' => session('user_id'),
            'user_email' => session('user_email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'status' => $status,
        ]);
    }
}
