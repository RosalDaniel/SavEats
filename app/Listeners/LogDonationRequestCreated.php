<?php

namespace App\Listeners;

use App\Events\DonationRequestCreated;
use App\Models\SystemLog;

class LogDonationRequestCreated
{
    /**
     * Handle the event.
     */
    public function handle(DonationRequestCreated $event): void
    {
        $request = $event->donationRequest;
        $request->load(['establishment', 'foodbank']);

        SystemLog::log(
            'donation_request',
            'donation_request_created',
            sprintf(
                'Donation request #%s for %s (%d %s) submitted by %s to %s',
                substr($request->donation_request_id, 0, 8),
                $request->item_name,
                $request->quantity,
                $request->unit ?? 'pcs',
                $request->establishment ? $request->establishment->business_name : 'Unknown Establishment',
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
                'unit' => $request->unit ?? 'pcs',
                'category' => $request->category,
                'status' => $request->status,
                'pickup_method' => $request->pickup_method ?? $request->delivery_option ?? 'pickup',
                'scheduled_date' => $request->scheduled_date ? $request->scheduled_date->format('Y-m-d') : null,
            ]
        );
    }
}

