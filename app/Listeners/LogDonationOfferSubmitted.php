<?php

namespace App\Listeners;

use App\Events\DonationOfferSubmitted;
use App\Models\SystemLog;

class LogDonationOfferSubmitted
{
    /**
     * Handle the event.
     */
    public function handle(DonationOfferSubmitted $event): void
    {
        $donation = $event->donation;
        $donation->load(['establishment', 'foodbank']);

        SystemLog::log(
            'donation',
            'donation_offer_submitted',
            sprintf(
                'Donation offer #%s for %s (%d %s) submitted by %s to %s',
                substr($donation->donation_id, 0, 8),
                $donation->item_name,
                $donation->quantity,
                $donation->unit ?? 'pcs',
                $donation->establishment ? $donation->establishment->business_name : 'Unknown Establishment',
                $donation->foodbank ? $donation->foodbank->organization_name : 'Unknown Foodbank'
            ),
            'info',
            'success',
            [
                'donation_id' => $donation->donation_id,
                'donation_number' => $donation->donation_number,
                'foodbank_id' => $donation->foodbank_id,
                'foodbank_name' => $donation->foodbank ? $donation->foodbank->organization_name : null,
                'establishment_id' => $donation->establishment_id,
                'establishment_name' => $donation->establishment ? $donation->establishment->business_name : null,
                'item_name' => $donation->item_name,
                'quantity' => $donation->quantity,
                'status' => $donation->status,
                'pickup_method' => $donation->pickup_method,
                'scheduled_date' => $donation->scheduled_date ? $donation->scheduled_date->format('Y-m-d') : null,
            ]
        );
    }
}

