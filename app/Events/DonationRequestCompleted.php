<?php

namespace App\Events;

use App\Models\DonationRequest;
use App\Models\Donation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonationRequestCompleted
{
    use Dispatchable, SerializesModels;

    public $donationRequest;
    public $donation;
    public $method;
    public $actingUserId;
    public $actingUserType;

    /**
     * Create a new event instance.
     */
    public function __construct(DonationRequest $donationRequest, Donation $donation, $method = 'pickup', $actingUserId = null, $actingUserType = null)
    {
        $this->donationRequest = $donationRequest;
        $this->donation = $donation;
        $this->method = $method;
        $this->actingUserId = $actingUserId ?? session('user_id');
        $this->actingUserType = $actingUserType ?? session('user_type');
    }
}

