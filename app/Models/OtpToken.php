<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpToken extends Model
{
    protected $fillable = [
        'user_type',
        'user_id',
        'phone_no',
        'otp',
        'purpose',
        'attempts',
        'expires_at',
        'verified_at',
        'used',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'used' => 'boolean',
        'attempts' => 'integer',
    ];

    /**
     * Check if OTP is valid and not expired
     */
    public function isValid()
    {
        return !$this->used && $this->expires_at->isFuture() && $this->attempts < 3;
    }

    /**
     * Increment attempt count
     */
    public function incrementAttempts()
    {
        $this->increment('attempts');
    }

    /**
     * Mark OTP as verified and used
     */
    public function markAsVerified()
    {
        $this->update([
            'verified_at' => now(),
            'used' => true,
        ]);
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired()
    {
        return self::where('expires_at', '<', now())->delete();
    }
}
