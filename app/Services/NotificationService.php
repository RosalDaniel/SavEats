<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\Donation;
use App\Models\DonationRequest;

class NotificationService
{
    /**
     * Create a notification for order placed
     */
    public static function notifyOrderPlaced(Order $order)
    {
        // Notify establishment about new order
        try {
            $itemName = $order->foodListing ? $order->foodListing->name : 'item';
            Notification::createNotification(
                'establishment',
                $order->establishment_id,
                'order_placed',
                'New Order Received',
                "You have received a new order #{$order->order_number} for {$order->quantity} x {$itemName}",
                [
                    'order_id' => $order->id,
                    'data' => [
                        'order_number' => $order->order_number,
                        'quantity' => $order->quantity,
                        'total_price' => $order->total_price,
                    ]
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Failed to create notification for establishment: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'establishment_id' => $order->establishment_id
            ]);
            // Don't throw - allow order to complete even if notification fails
        }
        
        // Also notify consumer that their order was placed successfully
        try {
            Notification::createNotification(
                'consumer',
                $order->consumer_id,
                'order_placed',
                'Order Placed Successfully',
                "Your order #{$order->order_number} has been placed successfully. Waiting for establishment confirmation.",
                [
                    'order_id' => $order->id,
                    'data' => [
                        'order_number' => $order->order_number,
                        'quantity' => $order->quantity,
                        'total_price' => $order->total_price,
                    ]
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Failed to create notification for consumer: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'consumer_id' => $order->consumer_id
            ]);
            // Don't throw - allow order to complete even if notification fails
        }
    }

    /**
     * Create a notification for order accepted
     */
    public static function notifyOrderAccepted(Order $order)
    {
        // Notify consumer that order was accepted
        $establishmentName = $order->establishment ? $order->establishment->business_name : 'the establishment';
        Notification::createNotification(
            'consumer',
            $order->consumer_id,
            'order_accepted',
            'Order Accepted',
            "Your order #{$order->order_number} has been accepted by {$establishmentName}",
            [
                'order_id' => $order->id,
                'data' => [
                    'order_number' => $order->order_number,
                    'establishment_name' => $order->establishment ? $order->establishment->business_name : null,
                ]
            ]
        );
    }

    /**
     * Create a notification for order out for delivery
     */
    public static function notifyOrderOutForDelivery(Order $order)
    {
        // Notify consumer that order is out for delivery
        $establishmentName = $order->establishment ? $order->establishment->business_name : 'the establishment';
        Notification::createNotification(
            'consumer',
            $order->consumer_id,
            'order_out_for_delivery',
            'Order Out for Delivery',
            "Your order #{$order->order_number} is now out for delivery from {$establishmentName}. Please be ready to receive your order.",
            [
                'order_id' => $order->id,
                'data' => [
                    'order_number' => $order->order_number,
                    'establishment_name' => $order->establishment ? $order->establishment->business_name : null,
                ]
            ]
        );
    }

    /**
     * Create a notification for order cancelled
     */
    public static function notifyOrderCancelled(Order $order, $cancelledBy = 'establishment')
    {
        if ($cancelledBy === 'establishment') {
            // Notify consumer that establishment cancelled
            $establishmentName = $order->establishment ? $order->establishment->business_name : 'the establishment';
            Notification::createNotification(
                'consumer',
                $order->consumer_id,
                'order_cancelled',
                'Order Cancelled',
                "Your order #{$order->order_number} has been cancelled by {$establishmentName}",
                [
                    'order_id' => $order->id,
                    'data' => [
                        'order_number' => $order->order_number,
                        'reason' => $order->cancellation_reason,
                        'cancelled_by' => 'establishment',
                    ]
                ]
            );
        } else {
            // Notify establishment that consumer cancelled
            Notification::createNotification(
                'establishment',
                $order->establishment_id,
                'order_cancelled',
                'Order Cancelled by Customer',
                "Order #{$order->order_number} has been cancelled by the customer",
                [
                    'order_id' => $order->id,
                    'data' => [
                        'order_number' => $order->order_number,
                        'reason' => $order->cancellation_reason,
                        'cancelled_by' => 'consumer',
                    ]
                ]
            );
        }
    }

    /**
     * Create a notification for order completed
     */
    public static function notifyOrderCompleted(Order $order)
    {
        // Notify consumer that order is completed
        Notification::createNotification(
            'consumer',
            $order->consumer_id,
            'order_completed',
            'Order Completed',
            "Your order #{$order->order_number} has been completed. Thank you for your order!",
            [
                'order_id' => $order->id,
                'data' => [
                    'order_number' => $order->order_number,
                ]
            ]
        );
    }

    /**
     * Create a notification for donation request
     */
    public static function notifyDonationRequested(DonationRequest $donationRequest)
    {
        // Notify foodbank about new donation request
        Notification::createNotification(
            'foodbank',
            $donationRequest->foodbank_id,
            'donation_requested',
            'New Donation Request',
            "You have received a new donation request for {$donationRequest->item_name}",
            [
                'donation_request_id' => $donationRequest->donation_request_id,
                'data' => [
                    'item_name' => $donationRequest->item_name,
                    'quantity' => $donationRequest->quantity,
                ]
            ]
        );
    }

    /**
     * Create a notification for donation created/offered
     */
    public static function notifyDonationCreated(Donation $donation)
    {
        // Notify foodbank about new donation offer
        $establishmentName = $donation->establishment ? $donation->establishment->business_name : 'an establishment';
        Notification::createNotification(
            'foodbank',
            $donation->foodbank_id,
            'donation_offered',
            'New Donation Offer',
            "You have received a new donation offer for {$donation->item_name} from {$establishmentName}",
            [
                'donation_id' => $donation->donation_id,
                'data' => [
                    'item_name' => $donation->item_name,
                    'quantity' => $donation->quantity,
                    'establishment_name' => $donation->establishment ? $donation->establishment->business_name : null,
                ]
            ]
        );
    }

    /**
     * Create a notification for donation approved/accepted
     */
    public static function notifyDonationApproved(Donation $donation)
    {
        // Notify establishment that donation was approved
        $foodbankName = $donation->foodbank ? $donation->foodbank->organization_name : 'the foodbank';
        Notification::createNotification(
            'establishment',
            $donation->establishment_id,
            'donation_approved',
            'Donation Approved',
            "Your donation for {$donation->item_name} has been approved by {$foodbankName}",
            [
                'donation_id' => $donation->donation_id,
                'data' => [
                    'item_name' => $donation->item_name,
                    'quantity' => $donation->quantity,
                    'foodbank_name' => $donation->foodbank ? $donation->foodbank->organization_name : null,
                ]
            ]
        );
    }

    /**
     * Create a notification for donation collected
     */
    public static function notifyDonationCollected(Donation $donation)
    {
        // Notify establishment that donation was collected
        $foodbankName = $donation->foodbank ? $donation->foodbank->organization_name : 'the foodbank';
        Notification::createNotification(
            'establishment',
            $donation->establishment_id,
            'donation_collected',
            'Donation Collected',
            "Your donation for {$donation->item_name} has been collected by {$foodbankName}",
            [
                'donation_id' => $donation->donation_id,
                'data' => [
                    'item_name' => $donation->item_name,
                    'quantity' => $donation->quantity,
                ]
            ]
        );
    }

    /**
     * Notify when donation request is accepted
     */
    public static function notifyDonationRequestAccepted(DonationRequest $donationRequest)
    {
        $foodbankName = $donationRequest->foodbank ? $donationRequest->foodbank->organization_name : 'the foodbank';
        $establishmentName = $donationRequest->establishment ? $donationRequest->establishment->business_name : 'an establishment';
        $establishmentAddress = $donationRequest->establishment ? ($donationRequest->establishment->address ?? 'Address not provided') : 'Address not provided';
        
        // Notify establishment
        Notification::createNotification(
            'establishment',
            $donationRequest->establishment_id,
            'donation_request_accepted',
            'Donation Request Accepted',
            "Your donation request for {$donationRequest->item_name} has been accepted by {$foodbankName}. Please prepare the items for pickup.",
            [
                'donation_request_id' => $donationRequest->donation_request_id,
                'data' => [
                    'item_name' => $donationRequest->item_name,
                    'quantity' => $donationRequest->quantity,
                    'pickup_method' => 'pickup',
                ]
            ]
        );
        
        // Notify foodbank (confirmation) - include establishment location for pickup
        Notification::createNotification(
            'foodbank',
            $donationRequest->foodbank_id,
            'donation_request_accepted',
            'Donation Request Accepted',
            "You have accepted the donation request for {$donationRequest->item_name} from {$establishmentName}. Pickup location: {$establishmentAddress}",
            [
                'donation_request_id' => $donationRequest->donation_request_id,
                'data' => [
                    'item_name' => $donationRequest->item_name,
                    'quantity' => $donationRequest->quantity,
                    'establishment_name' => $establishmentName,
                    'establishment_address' => $establishmentAddress,
                ]
            ]
        );
    }

    /**
     * Notify when donation request is declined
     */
    public static function notifyDonationRequestDeclined(DonationRequest $donationRequest)
    {
        $establishmentName = $donationRequest->establishment ? $donationRequest->establishment->business_name : 'an establishment';
        
        // Notify establishment
        Notification::createNotification(
            'establishment',
            $donationRequest->establishment_id,
            'donation_request_declined',
            'Donation Request Declined',
            "Your donation request for {$donationRequest->item_name} has been declined by the foodbank.",
            [
                'donation_request_id' => $donationRequest->donation_request_id,
                'data' => [
                    'item_name' => $donationRequest->item_name,
                    'quantity' => $donationRequest->quantity,
                ]
            ]
        );
        
        // Notify foodbank (confirmation)
        Notification::createNotification(
            'foodbank',
            $donationRequest->foodbank_id,
            'donation_request_declined',
            'Donation Request Declined',
            "You have declined the donation request for {$donationRequest->item_name} from {$establishmentName}.",
            [
                'donation_request_id' => $donationRequest->donation_request_id,
                'data' => [
                    'item_name' => $donationRequest->item_name,
                    'quantity' => $donationRequest->quantity,
                    'establishment_name' => $establishmentName,
                ]
            ]
        );
    }

    /**
     * Notify when donation request is completed
     */
    public static function notifyDonationRequestCompleted(DonationRequest $donationRequest, Donation $donation)
    {
        $foodbankName = $donationRequest->foodbank ? $donationRequest->foodbank->organization_name : 'the foodbank';
        $establishmentName = $donationRequest->establishment ? $donationRequest->establishment->business_name : 'an establishment';
        
        // Notify establishment
        Notification::createNotification(
            'establishment',
            $donationRequest->establishment_id,
            'donation_request_completed',
            'Donation Request Completed',
            "Your donation request for {$donationRequest->item_name} has been completed. The pickup was successful!",
            [
                'donation_request_id' => $donationRequest->donation_request_id,
                'donation_id' => $donation->donation_id,
                'data' => [
                    'item_name' => $donationRequest->item_name,
                    'quantity' => $donationRequest->quantity,
                    'donation_number' => $donation->donation_number,
                ]
            ]
        );
        
        // Notify foodbank
        Notification::createNotification(
            'foodbank',
            $donationRequest->foodbank_id,
            'donation_request_completed',
            'Donation Request Completed',
            "You have successfully completed the donation request for {$donationRequest->item_name} from {$establishmentName}. Pickup confirmed.",
            [
                'donation_request_id' => $donationRequest->donation_request_id,
                'donation_id' => $donation->donation_id,
                'data' => [
                    'item_name' => $donationRequest->item_name,
                    'quantity' => $donationRequest->quantity,
                    'donation_number' => $donation->donation_number,
                    'establishment_name' => $establishmentName,
                ]
            ]
        );
    }
}

