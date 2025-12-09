<?php

namespace App\Services;

use App\Models\DonationRequest;
use App\Models\Donation;
use App\Models\Foodbank;
use App\Events\DonationRequestAccepted as DonationRequestAcceptedEvent;
use App\Events\DonationRequestDeclined as DonationRequestDeclinedEvent;
use App\Events\DonationRequestCompleted as DonationRequestCompletedEvent;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class DonationRequestService
{
    /**
     * Standard status progression
     */
    const STATUS_PENDING = 'pending';              // Incoming
    const STATUS_ACCEPTED = 'accepted';            // Accepted
    const STATUS_PENDING_CONFIRMATION = 'pending_confirmation'; // Pending Confirmation
    const STATUS_COMPLETED = 'completed';          // Completed
    const STATUS_DECLINED = 'declined';            // Declined

    /**
     * Accept a donation request
     */
    public static function acceptRequest(DonationRequest $donationRequest): bool
    {
        if (!in_array($donationRequest->status, [self::STATUS_PENDING, self::STATUS_PENDING_CONFIRMATION])) {
            return false;
        }

        $donationRequest->status = self::STATUS_ACCEPTED;
        $donationRequest->skipObserverLogging = true; // Prevent duplicate logging from observer
        // Use saveQuietly to prevent observer from running (and prevent skipObserverLogging from being saved)
        $donationRequest->saveQuietly();

        // Send notifications
        $donationRequest->load(['foodbank', 'establishment']);
        NotificationService::notifyDonationRequestAccepted($donationRequest);

        // Dispatch event for logging
        event(new DonationRequestAcceptedEvent($donationRequest));

        return true;
    }

    /**
     * Decline a donation request
     */
    public static function declineRequest(DonationRequest $donationRequest): bool
    {
        if (!in_array($donationRequest->status, [self::STATUS_PENDING, self::STATUS_PENDING_CONFIRMATION])) {
            return false;
        }

        $donationRequest->status = self::STATUS_DECLINED;
        $donationRequest->skipObserverLogging = true; // Prevent duplicate logging from observer
        // Use saveQuietly to prevent observer from running (and prevent skipObserverLogging from being saved)
        $donationRequest->saveQuietly();

        // Send notifications
        $donationRequest->load(['foodbank', 'establishment']);
        NotificationService::notifyDonationRequestDeclined($donationRequest);

        // Dispatch event for logging
        event(new DonationRequestDeclinedEvent($donationRequest));

        return true;
    }

    /**
     * Confirm pickup and mark as completed (pickup-only)
     */
    public static function confirmCompletion(DonationRequest $donationRequest, string $method = 'pickup'): ?Donation
    {
        // Only accept or pending_confirmation can be confirmed
        if (!in_array($donationRequest->status, [self::STATUS_ACCEPTED, self::STATUS_PENDING_CONFIRMATION])) {
            return null;
        }

        // Force method to 'pickup' (delivery is no longer supported)
        $method = 'pickup';

        // Create Donation record
        $donation = Donation::create([
            'foodbank_id' => $donationRequest->foodbank_id,
            'establishment_id' => $donationRequest->establishment_id,
            'donation_request_id' => $donationRequest->donation_request_id,
            'item_name' => $donationRequest->item_name,
            'item_category' => $donationRequest->category,
            'quantity' => $donationRequest->quantity,
            'unit' => $donationRequest->unit ?? 'pcs',
            'description' => $donationRequest->description,
            'expiry_date' => $donationRequest->expiry_date,
            'status' => 'collected',
            'pickup_method' => 'pickup', // Always pickup
            'scheduled_date' => $donationRequest->scheduled_date ?? $donationRequest->dropoff_date ?? now(),
            'scheduled_time' => $donationRequest->scheduled_time,
            'establishment_notes' => $donationRequest->establishment_notes,
            'collected_at' => now(),
            'is_urgent' => false,
            'is_nearing_expiry' => false,
        ]);

        // Update request status
        $donationRequest->status = self::STATUS_COMPLETED;
        $donationRequest->donation_id = $donation->donation_id;
        $donationRequest->fulfilled_at = now();
        $donationRequest->skipObserverLogging = true; // Prevent duplicate logging from observer
        // Use saveQuietly to prevent observer from running (and prevent skipObserverLogging from being saved)
        $donationRequest->saveQuietly();

        // Send notifications
        $donationRequest->load(['establishment', 'foodbank']);
        NotificationService::notifyDonationRequestCompleted($donationRequest, $donation);

        // Dispatch event for logging (always with pickup method)
        event(new DonationRequestCompletedEvent($donationRequest, $donation, 'pickup'));

        return $donation;
    }

    /**
     * Format donation request data for display
     */
    public static function formatRequestData(DonationRequest $request): array
    {
        // Format time display
        $timeDisplay = 'N/A';
        if ($request->time_option === 'allDay') {
            $timeDisplay = 'All Day';
        } elseif ($request->time_option === 'anytime') {
            $timeDisplay = 'Anytime';
        } elseif ($request->start_time && $request->end_time) {
            $start = is_string($request->start_time) 
                ? date('g:i A', strtotime($request->start_time))
                : $request->start_time->format('g:i A');
            $end = is_string($request->end_time)
                ? date('g:i A', strtotime($request->end_time))
                : $request->end_time->format('g:i A');
            $timeDisplay = "{$start} - {$end}";
        }

        // Format distribution zones
        $zoneLabels = [
            'zone-a' => 'Zone A - North District',
            'zone-b' => 'Zone B - South District',
            'zone-c' => 'Zone C - East District',
            'zone-d' => 'Zone D - West District',
            'zone-e' => 'Zone E - Central District'
        ];

        return [
            'id' => $request->donation_request_id,
            'item_name' => $request->item_name,
            'quantity' => $request->quantity,
            'unit' => $request->unit ?? 'pcs',
            'category' => $request->category ?? 'other',
            'description' => $request->description,
            'expiry_date' => $request->expiry_date ? $request->expiry_date->format('Y-m-d') : null,
            'expiry_date_display' => $request->expiry_date ? $request->expiry_date->format('F d, Y') : 'N/A',
            'distribution_zone' => $request->distribution_zone,
            'distribution_zone_display' => $zoneLabels[$request->distribution_zone] ?? $request->distribution_zone ?? 'N/A',
            'dropoff_date' => $request->dropoff_date ? $request->dropoff_date->format('Y-m-d') : null,
            'dropoff_date_display' => $request->dropoff_date ? $request->dropoff_date->format('F d, Y') : 'N/A',
            'scheduled_date' => $request->scheduled_date ? ($request->scheduled_date instanceof \Carbon\Carbon ? $request->scheduled_date->format('Y-m-d') : (is_string($request->scheduled_date) ? $request->scheduled_date : date('Y-m-d', strtotime($request->scheduled_date)))) : null,
            'scheduled_date_display' => $request->scheduled_date ? ($request->scheduled_date instanceof \Carbon\Carbon ? $request->scheduled_date->format('F d, Y') : (is_string($request->scheduled_date) ? date('F d, Y', strtotime($request->scheduled_date)) : 'N/A')) : 'N/A',
            'scheduled_time' => $request->scheduled_time ? (is_string($request->scheduled_time) ? substr($request->scheduled_time, 0, 5) : ($request->scheduled_time instanceof \Carbon\Carbon ? $request->scheduled_time->format('H:i') : null)) : null,
            'scheduled_time_display' => $request->scheduled_time ? (is_string($request->scheduled_time) ? date('g:i A', strtotime($request->scheduled_time)) : ($request->scheduled_time instanceof \Carbon\Carbon ? $request->scheduled_time->format('g:i A') : 'N/A')) : 'N/A',
            'time_option' => $request->time_option,
            'start_time' => $request->start_time ? (is_string($request->start_time) ? substr($request->start_time, 0, 5) : $request->start_time->format('H:i')) : null,
            'end_time' => $request->end_time ? (is_string($request->end_time) ? substr($request->end_time, 0, 5) : $request->end_time->format('H:i')) : null,
            'time_display' => $timeDisplay,
            'address' => $request->address,
            'pickup_method' => 'pickup',
            'pickup_method_display' => 'Pickup',
            'establishment_notes' => $request->establishment_notes,
            'contact_name' => $request->contact_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'status' => $request->status,
            'matches' => $request->matches ?? 0,
            'donation_id' => $request->donation_id,
            'donation_number' => $request->donation ? $request->donation->donation_number : null,
            'establishment_id' => $request->establishment_id,
            'establishment_name' => $request->establishment ? $request->establishment->business_name : null,
            'foodbank_id' => $request->foodbank_id,
            'foodbank_name' => $request->foodbank ? $request->foodbank->organization_name : null,
            'foodbank_email' => $request->foodbank ? $request->foodbank->email : null,
            'foodbank_phone' => $request->foodbank ? $request->foodbank->phone_no : null,
            'fulfilled_by_establishment_id' => $request->fulfilled_by_establishment_id,
            'fulfilled_at' => $request->fulfilled_at ? $request->fulfilled_at->format('Y-m-d H:i:s') : null,
            'fulfilled_at_display' => $request->fulfilled_at ? $request->fulfilled_at->format('F d, Y g:i A') : null,
            'accepted_at_display' => $request->status === self::STATUS_ACCEPTED ? $request->updated_at->format('F d, Y g:i A') : null,
            'created_at_display' => $request->created_at->format('F d, Y g:i A'),
            'updated_at_display' => $request->updated_at->format('F d, Y g:i A'),
        ];
    }

    /**
     * Get status display name
     */
    public static function getStatusDisplay(string $status): string
    {
        $statusMap = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_PENDING_CONFIRMATION => 'Pending Confirmation',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_DECLINED => 'Declined',
        ];

        return $statusMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Check if status can transition to target status
     */
    public static function canTransitionTo(string $currentStatus, string $targetStatus): bool
    {
        $validTransitions = [
            self::STATUS_PENDING => [self::STATUS_ACCEPTED, self::STATUS_DECLINED],
            self::STATUS_ACCEPTED => [self::STATUS_PENDING_CONFIRMATION, self::STATUS_DECLINED],
            self::STATUS_PENDING_CONFIRMATION => [self::STATUS_COMPLETED],
        ];

        return in_array($targetStatus, $validTransitions[$currentStatus] ?? []);
    }
}

