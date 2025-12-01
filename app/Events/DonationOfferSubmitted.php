<?php

namespace App\Events;

use App\Models\Donation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DonationOfferSubmitted
{
    use Dispatchable, SerializesModels;

    public $donation;
    public $actingUserId;
    public $actingUserType;

    /**
     * Create a new event instance.
     */
    public function __construct(Donation $donation, $actingUserId = null, $actingUserType = null)
    {
        $this->donation = $donation;
        $this->actingUserId = $actingUserId ?? session('user_id');
        $this->actingUserType = $actingUserType ?? session('user_type');
    }
}

