<?php

namespace App\Listeners;

use App\Events\DonationRequestCompleted;
use App\Models\SystemLog;

class LogDonationRequestCompleted
{
    /**
     * Handle the event.
     */
    public function handle(DonationRequestCompleted $event): void
    {
        $request = $event->donationRequest;
        $donation = $event->donation;
        $method = $event->method;
        $request->load(['establishment', 'foodbank']);

        SystemLog::log(
            'donation_request',
            'donation_request_completed',
            sprintf(
                'Donation request #%s for %s (%d %s) completed via %s by %s. Donation #%s created.',
                substr($request->donation_request_id, 0, 8),
                $request->item_name,
                $request->quantity,
                $request->unit ?? 'pcs',
                $method,
                $request->foodbank ? $request->foodbank->organization_name : 'Unknown Foodbank',
                substr($donation->donation_id, 0, 8)
            ),
            'info',
            'success',
            [
                'donation_request_id' => $request->donation_request_id,
                'donation_id' => $donation->donation_id,
                'donation_number' => $donation->donation_number,
                'foodbank_id' => $request->foodbank_id,
                'foodbank_name' => $request->foodbank ? $request->foodbank->organization_name : null,
                'establishment_id' => $request->establishment_id,
                'establishment_name' => $request->establishment ? $request->establishment->business_name : null,
                'item_name' => $request->item_name,
                'quantity' => $request->quantity,
                'method' => $method,
                'status' => $request->status,
            ]
        );
    }
}

