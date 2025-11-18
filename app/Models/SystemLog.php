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
