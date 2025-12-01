<?php

namespace App\Listeners;

use App\Events\DonationOfferDeclined;
use App\Models\SystemLog;

class LogDonationOfferDeclined
{
    /**
     * Handle the event.
     */
    public function handle(DonationOfferDeclined $event): void
    {
        $donation = $event->donation;
        $donationRequest = $event->donationRequest;
        $donation->load(['establishment', 'foodbank']);

        SystemLog::log(
            'donation',
            'donation_offer_declined',
            sprintf(
                'Donation offer #%s for %s (%d %s) declined by %s',
                substr($donation->donation_id, 0, 8),
                $donation->item_name,
                $donation->quantity,
                $donation->unit ?? 'pcs',
                $donation->foodbank ? $donation->foodbank->organization_name : 'Unknown Foodbank'
            ),
            'info',
            'success',
            [
                'donation_id' => $donation->donation_id,
                'donation_number' => $donation->donation_number,
                'donation_request_id' => $donationRequest ? $donationRequest->donation_request_id : null,
                'foodbank_id' => $donation->foodbank_id,
                'foodbank_name' => $donation->foodbank ? $donation->foodbank->organization_name : null,
                'establishment_id' => $donation->establishment_id,
                'establishment_name' => $donation->establishment ? $donation->establishment->business_name : null,
                'item_name' => $donation->item_name,
                'quantity' => $donation->quantity,
                'status' => $donation->status,
            ]
        );
    }
}

