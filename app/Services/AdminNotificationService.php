<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;
use App\Models\Review;
use App\Models\Donation;
use App\Models\DonationRequest;
use App\Models\Order;

class AdminNotificationService
{
    /**
     * Notify admin about new user registration
     */
    public static function notifyUserRegistered($userType, $userId, $userData = [])
    {
        $userName = $userData['name'] ?? 'User';
        $priority = 'normal';

        AdminNotification::createNotification(
            'user_registered',
            'New User Registration',
            "A new {$userType} has registered: {$userName}",
            $priority,
            [
                'user_type' => $userType,
                'user_id' => $userId,
                'data' => $userData
            ]
        );
    }

    /**
     * Notify admin about flagged review
     */
    public static function notifyReviewFlagged(Review $review, $reason = null)
    {
        $reviewerName = $review->consumer ? $review->consumer->fname . ' ' . $review->consumer->lname : 'Unknown';
        $establishmentName = $review->establishment ? $review->establishment->business_name : 'Unknown';

        AdminNotification::createNotification(
            'review_flagged',
            'Review Flagged',
            "Review by {$reviewerName} for {$establishmentName} has been flagged" . ($reason ? ": {$reason}" : ''),
            'high',
            [
                'review_id' => $review->id,
                'user_type' => 'consumer',
                'user_id' => $review->consumer_id,
                'data' => [
                    'reason' => $reason,
                    'review_rating' => $review->rating,
                ]
            ]
        );
    }

    /**
     * Notify admin about donation issue
     */
    public static function notifyDonationIssue(Donation $donation, $issueType, $message)
    {
        $priority = $issueType === 'urgent' ? 'urgent' : 'high';

        AdminNotification::createNotification(
            'donation_issue',
            'Donation Issue',
            $message,
            $priority,
            [
                'donation_id' => $donation->donation_id,
                'user_type' => 'establishment',
                'user_id' => $donation->establishment_id,
                'data' => [
                    'issue_type' => $issueType,
                    'donation_number' => $donation->donation_number,
                ]
            ]
        );
    }

    /**
     * Notify admin about pending donation request (too long)
     */
    public static function notifyDonationRequestPending(DonationRequest $donationRequest, $daysPending)
    {
        AdminNotification::createNotification(
            'donation_request_pending',
            'Donation Request Pending Too Long',
            "Donation request for {$donationRequest->item_name} has been pending for {$daysPending} days",
            $daysPending > 7 ? 'urgent' : 'high',
            [
                'donation_request_id' => $donationRequest->donation_request_id,
                'user_type' => 'foodbank',
                'user_id' => $donationRequest->foodbank_id,
                'data' => [
                    'days_pending' => $daysPending,
                    'item_name' => $donationRequest->item_name,
                ]
            ]
        );
    }

    /**
     * Notify admin about account deletion request
     */
    public static function notifyAccountDeletionRequest($deletionRequestId, $userType, $userId, $userName, $reason = null)
    {
        AdminNotification::createNotification(
            'account_deletion_request',
            'Account Deletion Request',
            "{$userType} account deletion requested: {$userName}" . ($reason ? " - Reason: {$reason}" : ''),
            'high',
            [
                'deletion_request_id' => $deletionRequestId,
                'user_type' => $userType,
                'user_id' => $userId,
                'data' => [
                    'user_name' => $userName,
                    'reason' => $reason,
                ]
            ]
        );
    }

    /**
     * Notify admin about system alert
     */
    public static function notifySystemAlert($title, $message, $priority = 'high', $data = [])
    {
        AdminNotification::createNotification(
            'system_alert',
            $title,
            $message,
            $priority,
            [
                'data' => $data
            ]
        );
    }

    /**
     * Notify admin about account suspension
     */
    public static function notifyAccountSuspended($userType, $userId, $userName, $reason = null)
    {
        AdminNotification::createNotification(
            'account_suspended',
            'Account Suspended',
            "{$userType} account suspended: {$userName}" . ($reason ? " - Reason: {$reason}" : ''),
            'high',
            [
                'user_type' => $userType,
                'user_id' => $userId,
                'data' => [
                    'user_name' => $userName,
                    'reason' => $reason,
                ]
            ]
        );
    }

    /**
     * Notify admin about order issue
     */
    public static function notifyOrderIssue(Order $order, $issueType, $message)
    {
        AdminNotification::createNotification(
            'order_issue',
            'Order Issue',
            $message,
            'high',
            [
                'order_id' => $order->id,
                'user_type' => 'consumer',
                'user_id' => $order->consumer_id,
                'data' => [
                    'issue_type' => $issueType,
                    'order_number' => $order->order_number,
                ]
            ]
        );
    }
}

