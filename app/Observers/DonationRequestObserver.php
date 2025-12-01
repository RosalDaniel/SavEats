<?php

namespace App\Observers;

use App\Models\DonationRequest;
use App\Events\DonationRequestCreated;
use App\Events\DonationRequestAccepted;
use App\Events\DonationRequestDeclined;

class DonationRequestObserver
{
    /**
     * Handle the DonationRequest "created" event.
     */
    public function created(DonationRequest $donationRequest): void
    {
        // Only dispatch if it's an establishment-initiated request
        // (foodbank's own requests are handled differently)
        if ($donationRequest->establishment_id) {
            DonationRequestCreated::dispatch($donationRequest);
        }
    }

    /**
     * Handle the DonationRequest "updated" event.
     * 
     * Note: Status changes are typically handled by service methods that dispatch events directly.
     * This observer only logs status changes that occur outside of service methods.
     * To prevent duplicates, we check if the status change was made through a service method
     * by checking if the model has a 'skipObserverLogging' attribute set.
     */
    public function updated(DonationRequest $donationRequest): void
    {
        // Skip if explicitly marked to skip observer logging (prevents duplicates)
        if ($donationRequest->skipObserverLogging ?? false) {
            return;
        }

        // Only log status changes for establishment-initiated requests
        if (!$donationRequest->establishment_id) {
            return;
        }

        // Check if status changed
        if ($donationRequest->wasChanged('status')) {
            $oldStatus = $donationRequest->getOriginal('status');
            $newStatus = $donationRequest->status;

            // Dispatch appropriate event based on status change
            // Note: Service methods handle their own event dispatching, so this is a fallback
            if ($newStatus === 'accepted' && $oldStatus !== 'accepted') {
                DonationRequestAccepted::dispatch($donationRequest);
            } elseif ($newStatus === 'declined' && $oldStatus !== 'declined') {
                DonationRequestDeclined::dispatch($donationRequest);
            }
            // Note: Completed status is handled by DonationRequestService::confirmCompletion
            // which dispatches DonationRequestCompleted event directly
        }
    }
}

