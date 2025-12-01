<?php

namespace App\Listeners;

use App\Events\DonationRequestAccepted;
use App\Models\SystemLog;

class LogDonationRequestAccepted
{
    /**
     * Handle the event.
     */
    public function handle(DonationRequestAccepted $event): void
    {
        $request = $event->donationRequest;
        $request->load(['establishment', 'foodbank']);

        SystemLog::log(
            'donation_request',
            'donation_request_accepted',
            sprintf(
                'Donation request #%s for %s (%d %s) accepted by %s',
                substr($request->donation_request_id, 0, 8),
                $request->item_name,
                $request->quantity,
                $request->unit ?? 'pcs',
                $request->foodbank ? $request->foodbank->organization_name : 'Unknown Foodbank'
            ),
            'info',
            'success',
            [
                'donation_request_id' => $request->donation_request_id,
                'foodbank_id' => $request->foodbank_id,
                'foodbank_name' => $request->foodbank ? $request->foodbank->organization_name : null,
                'establishment_id' => $request->establishment_id,
                'establishment_name' => $request->establishment ? $request->establishment->business_name : null,
                'item_name' => $request->item_name,
                'quantity' => $request->quantity,
                'status' => $request->status,
            ]
        );
    }
}

