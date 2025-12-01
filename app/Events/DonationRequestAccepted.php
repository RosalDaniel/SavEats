<?php

namespace App\Events;

use App\Models\DonationRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonationRequestAccepted
{
    use Dispatchable, SerializesModels;

    public $donationRequest;
    public $actingUserId;
    public $actingUserType;

    /**
     * Create a new event instance.
     */
    public function __construct(DonationRequest $donationRequest, $actingUserId = null, $actingUserType = null)
    {
        $this->donationRequest = $donationRequest;
        $this->actingUserId = $actingUserId ?? session('user_id');
        $this->actingUserType = $actingUserType ?? session('user_type');
    }
}

