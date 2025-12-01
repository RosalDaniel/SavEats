<?php

namespace App\Events;

use App\Models\Donation;
use App\Models\DonationRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonationOfferAccepted
{
    use Dispatchable, SerializesModels;

    public $donation;
    public $donationRequest;
    public $actingUserId;
    public $actingUserType;

    /**
     * Create a new event instance.
     */
    public function __construct(Donation $donation, DonationRequest $donationRequest = null, $actingUserId = null, $actingUserType = null)
    {
        $this->donation = $donation;
        $this->donationRequest = $donationRequest;
        $this->actingUserId = $actingUserId ?? session('user_id');
        $this->actingUserType = $actingUserType ?? session('user_type');
    }
}

