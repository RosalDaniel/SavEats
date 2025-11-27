<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'user_type',
        'user_id',
        'is_read',
        'read_at',
        'order_id',
        'donation_id',
        'donation_request_id',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification (polymorphic)
     */
    public function user()
    {
        switch ($this->user_type) {
            case 'consumer':
                return $this->belongsTo(Consumer::class, 'user_id', 'consumer_id');
            case 'establishment':
                return $this->belongsTo(Establishment::class, 'user_id', 'establishment_id');
            case 'foodbank':
                return $this->belongsTo(Foodbank::class, 'user_id', 'foodbank_id');
            default:
                return null;
        }
    }

    /**
     * Get the order related to this notification
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the donation related to this notification
     */
    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donation_id');
    }

    /**
     * Get the donation request related to this notification
     */
    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'donation_request_id', 'donation_request_id');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope a query to filter by user type and ID.
     */
    public function scopeForUser($query, $userType, $userId)
    {
        return $query->where('user_type', $userType)
                     ->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by notification type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread()
    {
        if ($this->is_read) {
            $this->update([
                'is_read' => false,
                'read_at' => null,
            ]);
        }
    }

    /**
     * Static method to create a notification
     */
    public static function createNotification($userType, $userId, $type, $title, $message, $relatedData = [])
    {
        return self::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'user_type' => $userType,
            'user_id' => $userId,
            'order_id' => $relatedData['order_id'] ?? null,
            'donation_id' => $relatedData['donation_id'] ?? null,
            'donation_request_id' => $relatedData['donation_request_id'] ?? null,
            'data' => $relatedData['data'] ?? null,
        ]);
    }
}
