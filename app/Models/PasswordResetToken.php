<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordResetToken extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'email',
        'phone_no',
        'token',
        'recovery_method',
        'expires_at',
        'used',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Check if token is valid and not expired
     */
    public function isValid()
    {
        return !$this->used && $this->expires_at->isFuture();
    }

    /**
     * Mark token as used
     */
    public function markAsUsed()
    {
        $this->update(['used' => true]);
    }

    /**
     * Clean up expired tokens
     */
    public static function cleanupExpired()
    {
        return self::where('expires_at', '<', now())->delete();
    }
}
