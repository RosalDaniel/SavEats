<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class AdminNotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'title',
        'message',
        'priority',
        'is_read',
        'read_at',
        'read_by',
        'user_id',
        'user_type',
        'order_id',
        'review_id',
        'donation_id',
        'donation_request_id',
        'deletion_request_id',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the admin user who read this notification
     * Note: This assumes admin users are stored in the users table
     */
    public function readBy()
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    /**
     * Get the related order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the related review
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the related donation
     */
    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donation_id');
    }

    /**
     * Get the related donation request
     */
    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'donation_request_id', 'donation_request_id');
    }

    /**
     * Get the related user (consumer, establishment, or foodbank)
     */
    public function relatedUser()
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
     * Scope a query to filter by notification type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to get high priority notifications.
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($adminUserId = null)
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
                'read_by' => $adminUserId,
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
                'read_by' => null,
            ]);
        }
    }

    /**
     * Static method to create an admin notification
     */
    public static function createNotification($type, $title, $message, $priority = 'normal', $relatedData = [])
    {
        return self::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'user_id' => $relatedData['user_id'] ?? null,
            'user_type' => $relatedData['user_type'] ?? null,
            'order_id' => $relatedData['order_id'] ?? null,
            'review_id' => $relatedData['review_id'] ?? null,
            'donation_id' => $relatedData['donation_id'] ?? null,
            'donation_request_id' => $relatedData['donation_request_id'] ?? null,
            'deletion_request_id' => $relatedData['deletion_request_id'] ?? null,
            'data' => $relatedData['data'] ?? null,
        ]);
    }
}
