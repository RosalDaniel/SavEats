<?php

namespace App\Http\Controllers;

use App\Models\FoodListing;
use App\Models\Establishment;
use App\Models\Order;
use App\Models\Donation;
use App\Models\DonationRequest;
use App\Models\Foodbank;
use App\Models\Review;
use App\Models\Announcement;
use App\Services\NotificationService;
use App\Services\DonationRequestService;
use App\Services\DashboardCacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class EstablishmentController extends Controller
{
    public function dashboard()
    {
        // Check if user is logged in and is an establishment
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access the dashboard.');
        }

        $user = $this->getUserData();
        $establishmentId = Session::get('user_id');
        
        // Validate establishment ID exists
        if (!$establishmentId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }
        
        // Ensure establishment_id is a string (UUID)
        $establishmentId = (string) $establishmentId;
        
        // Verify establishment exists
        $establishment = Establishment::find($establishmentId);
        if (!$establishment) {
            return redirect()->route('login')->with('error', 'Establishment not found. Please login again.');
        }
        
        // Check verification status
        $isVerified = $establishment->isVerified();
        
        // Get cached dashboard stats
        $stats = DashboardCacheService::getEstablishmentStats($establishmentId);
        $dashboardStats = [
            'active_listings' => $stats['active_listings'],
            'today_earnings' => $stats['today_earnings'],
            'food_donated' => $stats['food_donated'],
            'food_saved' => $stats['food_saved'],
        ];
        $inventoryHealth = $stats['inventory_health'];
        
        // Get expiring food listings (expiring within next 3 days, not expired) - not cached
        $expiringItems = FoodListing::where('establishment_id', $establishmentId)
            ->where('expiry_date', '>=', now()->toDateString())
            ->where('expiry_date', '<=', now()->addDays(3)->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $expiryDate = \Carbon\Carbon::parse($item->expiry_date);
                
                // Format expiry date
                if ($expiryDate->isToday()) {
                    $expiryTime = 'Today, 6pm';
                } elseif ($expiryDate->isTomorrow()) {
                    $expiryTime = 'Tomorrow, 6pm';
                } else {
                    $expiryTime = $expiryDate->format('M j, Y');
                }
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'expiry_date' => $item->expiry_date->format('Y-m-d'),
                    'expiry_time' => $expiryTime,
                    'expiry_datetime' => $expiryDate,
                ];
            })
            ->values()
            ->toArray();
        
        // Get the most recent pending order - not cached as it changes frequently
        $pendingOrder = Order::with(['foodListing'])
            ->where('establishment_id', $establishmentId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
        
        $pendingOrderData = null;
        if ($pendingOrder && $pendingOrder->foodListing) {
            $pendingOrderData = [
                'id' => $pendingOrder->id,
                'order_number' => $pendingOrder->order_number ?? 'ID#' . $pendingOrder->id,
                'product_name' => $pendingOrder->foodListing->name,
                'quantity' => $pendingOrder->quantity,
                'total_price' => (float) $pendingOrder->total_price,
                'customer_name' => $pendingOrder->customer_name,
                'delivery_method' => ucfirst($pendingOrder->delivery_method),
            ];
        }
        
        // Get cached reviews data
        $reviewsData = DashboardCacheService::getEstablishmentReviews($establishmentId);
        
        // Get donation requests submitted by this establishment
        $myDonationRequests = DonationRequest::where('establishment_id', $establishmentId)
            ->with(['foodbank', 'donation'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($request) {
                // Format status display
                $statusMap = [
                    'pending' => 'Pending Review',
                    'accepted' => 'Accepted',
                    'pickup_confirmed' => 'Pickup Confirmed',
                    'delivery_successful' => 'Delivery Successful',
                    'completed' => 'Completed',
                    'declined' => 'Declined',
                ];
                $statusDisplay = $statusMap[$request->status] ?? ucfirst(str_replace('_', ' ', $request->status));
                
                return [
                    'id' => $request->donation_request_id,
                    'item_name' => $request->item_name,
                    'quantity' => $request->quantity,
                    'unit' => $request->unit,
                    'foodbank_name' => $request->foodbank ? $request->foodbank->organization_name : 'Unknown',
                    'status' => $request->status,
                    'status_display' => $statusDisplay,
                    'created_at' => $request->created_at->format('F d, Y'),
                    'scheduled_date' => $request->scheduled_date ? $request->scheduled_date->format('F d, Y') : 'N/A',
                    'updated_at' => $request->updated_at->format('F d, Y g:i A'),
                ];
            })
            ->toArray();
        
        return view('establishment.dashboard', compact('user', 'expiringItems', 'inventoryHealth', 'dashboardStats', 'pendingOrderData', 'reviewsData', 'myDonationRequests'));
    }

    /**
     * Get real-time reviews and ratings data (AJAX endpoint)
     */
    public function getRatings(Request $request)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $establishmentId = Session::get('user_id');
        
        // Fetch reviews and ratings data
        $reviews = Review::where('establishment_id', $establishmentId)
            ->with(['consumer'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate rating statistics
        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0;
        
        // Positive reviews (4-5 stars)
        $positiveReviews = $reviews->filter(function ($review) {
            return $review->rating >= 4;
        })->count();
        
        // Negative reviews (1-3 stars)
        $negativeReviews = $reviews->filter(function ($review) {
            return $review->rating <= 3;
        })->count();
        
        // Reviews this month
        $reviewsThisMonth = $reviews->filter(function ($review) {
            return $review->created_at->isCurrentMonth();
        })->count();
        
        // Calculate positive percentage
        $positivePercentage = $totalReviews > 0 
            ? round(($positiveReviews / $totalReviews) * 100) 
            : 0;
        
        // Format rating text
        $ratingText = '';
        if ($reviewsThisMonth > 0) {
            $ratingText = "You've received +{$reviewsThisMonth} review" . ($reviewsThisMonth !== 1 ? 's' : '') . " this month";
            if ($positivePercentage > 0) {
                $ratingText .= " - {$positivePercentage}% positive!";
            }
        } else {
            $ratingText = $totalReviews > 0 
                ? "You have {$totalReviews} total review" . ($totalReviews !== 1 ? 's' : '') . " - {$positivePercentage}% positive!"
                : "No reviews yet. Start selling to get reviews!";
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'average_rating' => $averageRating,
                'total_reviews' => $totalReviews,
                'positive_reviews' => $positiveReviews,
                'negative_reviews' => $negativeReviews,
                'reviews_this_month' => $reviewsThisMonth,
                'positive_percentage' => $positivePercentage,
                'rating_text' => $ratingText,
            ]
        ]);
    }

    /**
     * Format payment method for display
     */
    private function formatPaymentMethod($method)
    {
        $methods = [
            'cash' => 'Cash on Hand',
            'card' => 'Credit Card',
            'ewallet' => 'E-Wallet'
        ];
        
        return $methods[$method] ?? ucfirst($method);
    }

    /**
     * Get user data from session
     */
    private function getUserData()
    {
        return (object) [
            'id' => Session::get('user_id'),
            'name' => Session::get('user_name', 'User'),
            'fname' => Session::get('fname', ''),
            'lname' => Session::get('lname', ''),
            'email' => Session::get('user_email'),
            'user_type' => Session::get('user_type'),
        ];
    }

    /**
     * Check if the current establishment is verified
     */
    private function checkVerification()
    {
        $establishmentId = Session::get('user_id');
        if (!$establishmentId) {
            return false;
        }
        
        $establishment = Establishment::find($establishmentId);
        return $establishment && $establishment->isVerified();
    }

    public function listingManagement()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        $establishmentId = Session::get('user_id');
        
        // Get establishment data to access address
        $establishment = Establishment::find($establishmentId);
        $establishmentAddress = $establishment->address ?? '';
        $isVerified = $establishment && $establishment->isVerified();
        
        // Get real food listings from database
        $foodItems = FoodListing::where('establishment_id', $establishmentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                // Determine display status (expired, expiring, active, or inactive)
                $displayStatus = $item->status; // Use database status first
                if ($displayStatus === 'active') {
                    // Only check expiry if status is active
                    if ($item->is_expired) {
                        $displayStatus = 'expired';
                    } elseif ($item->expiry_date <= now()->addDays(3)) {
                        $displayStatus = 'expiring';
                    }
                }
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'category' => $item->category,
                    'quantity' => (string) $item->quantity,
                    'price' => $item->original_price,
                    'discounted_price' => $item->discounted_price,
                    'discount' => $item->discount_percentage,
                    'expiry' => $item->expiry_date->format('Y-m-d'),
                    'status' => $displayStatus,
                    'db_status' => $item->status, // Store actual database status
                    'is_disabled_by_admin' => $item->status === 'inactive',
                    'image' => $item->image_path ? Storage::url($item->image_path) : 'https://via.placeholder.com/40x40/4a7c59/ffffff?text=' . strtoupper(substr($item->name, 0, 1)),
                    'address' => $item->address,
                ];
            })
            ->toArray();

        $stats = [
            'total_items' => count($foodItems),
            'active_listings' => count(array_filter($foodItems, fn($item) => $item['status'] === 'active')),
            'expiring_soon' => count(array_filter($foodItems, fn($item) => $item['status'] === 'expiring')),
            'expired_items' => count(array_filter($foodItems, fn($item) => $item['status'] === 'expired')),
            'unsold_items' => count(array_filter($foodItems, fn($item) => $item['status'] === 'active' || $item['status'] === 'expiring'))
        ];

        return view('establishment.listing-management', compact('user', 'foodItems', 'stats', 'establishmentAddress', 'isVerified'));
    }

    public function orderManagement()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        // Get establishment data
        $establishmentId = Session::get('user_id');
        $establishment = Establishment::find($establishmentId);
        $isVerified = $establishment && $establishment->isVerified();
        
        if (!$establishment) {
            return redirect()->route('login')->with('error', 'Establishment not found.');
        }

        // Get real orders from database
        $orders = Order::with(['foodListing', 'consumer'])
            ->where('establishment_id', $establishmentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function ($order) {
                // Filter out orders with missing relationships
                return $order->foodListing !== null;
            })
            ->map(function ($order) {
                // Format pickup times
                $pickupStartTime = null;
                $pickupEndTime = null;
                if ($order->pickup_start_time) {
                    $pickupStartTime = is_string($order->pickup_start_time) 
                        ? substr($order->pickup_start_time, 0, 5) 
                        : $order->pickup_start_time->format('H:i');
                }
                if ($order->pickup_end_time) {
                    $pickupEndTime = is_string($order->pickup_end_time) 
                        ? substr($order->pickup_end_time, 0, 5) 
                        : $order->pickup_end_time->format('H:i');
                }
                
                // Format pickup date (use created_at date for pickup date, convert to Asia/Manila timezone)
                $pickupDate = $order->created_at ? \Carbon\Carbon::parse($order->created_at)->setTimezone('Asia/Manila')->format('F d, Y') : null;
                
                return [
                    'id' => $order->id,
                    'product_name' => $order->foodListing->name ?? 'Unknown Product',
                    'quantity' => $order->quantity . ' pcs.',
                    'price' => (float) $order->total_price,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone ?? '',
                    'delivery_method' => ucfirst($order->delivery_method),
                    'status' => $order->status,
                    'effective_status' => $order->effective_status, // Includes missed_pickup
                    'is_missed_pickup' => $order->isMissedPickup(),
                    'pickup_start_time' => $pickupStartTime,
                    'pickup_end_time' => $pickupEndTime,
                    'pickup_time_range' => $pickupStartTime && $pickupEndTime 
                        ? $pickupStartTime . ' - ' . $pickupEndTime 
                        : ($pickupEndTime ? $pickupEndTime : null),
                    'pickup_date' => $pickupDate,
                    'created_at' => $order->created_at
                ];
            })
            ->toArray();

        // Calculate order counts by status
        $orderCounts = [
            'pending' => count(array_filter($orders, fn($order) => $order['status'] === 'pending' && !$order['is_missed_pickup'])),
            'pending_delivery_confirmation' => count(array_filter($orders, fn($order) => $order['status'] === 'pending_delivery_confirmation' && !$order['is_missed_pickup'])),
            'accepted' => count(array_filter($orders, fn($order) => $order['status'] === 'accepted' && !$order['is_missed_pickup'])),
            'on_the_way' => count(array_filter($orders, fn($order) => $order['status'] === 'on_the_way' && !$order['is_missed_pickup'])),
            'missed_pickup' => count(array_filter($orders, fn($order) => $order['is_missed_pickup'])),
            'completed' => count(array_filter($orders, fn($order) => $order['status'] === 'completed')),
            'cancelled' => count(array_filter($orders, fn($order) => $order['status'] === 'cancelled'))
        ];

        return view('establishment.order-management', compact('establishment', 'orders', 'orderCounts', 'isVerified'));
    }

    public function announcements()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        $establishmentId = Session::get('user_id');
        $establishment = Establishment::find($establishmentId);
        $isVerified = $establishment && $establishment->isVerified();
        
        // Fetch announcements for establishments (all + establishment-specific)
        // Simplified query - show all active announcements for establishments/all, regardless of published_at/expires_at for now
        $announcements = Announcement::where('status', 'active')
            ->where(function($query) {
                $query->where('target_audience', 'all')
                      ->orWhere('target_audience', 'establishment');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Group announcements by date
        $groupedAnnouncements = $this->groupAnnouncementsByDate($announcements);
        
        return view('establishment.announcements', compact('user', 'announcements', 'groupedAnnouncements'));
    }
    
    /**
     * Group announcements by date (Today, Yesterday, A week ago, A month ago)
     */
    private function groupAnnouncementsByDate($announcements)
    {
        $now = now();
        $today = $now->copy()->startOfDay();
        $yesterday = $today->copy()->subDay();
        $weekAgo = $today->copy()->subWeek();
        $monthAgo = $today->copy()->subMonth();
        
        $grouped = [
            'today' => [],
            'yesterday' => [],
            'week' => [],
            'month' => []
        ];
        
        foreach ($announcements as $announcement) {
            $createdAt = $announcement->created_at;
            
            // Use Carbon's built-in date comparison methods
            if ($createdAt->isToday()) {
                $grouped['today'][] = $announcement;
            } elseif ($createdAt->isYesterday()) {
                $grouped['yesterday'][] = $announcement;
            } elseif ($createdAt->gte($weekAgo)) {
                // Created within the last week (but not today or yesterday)
                $grouped['week'][] = $announcement;
            } elseif ($createdAt->gte($monthAgo)) {
                // Created within the last month (but not within the last week)
                $grouped['month'][] = $announcement;
            } else {
                // If announcement is older than a month, still show it in the month section
                $grouped['month'][] = $announcement;
            }
        }
        
        return $grouped;
    }

    /**
     * Accept an order
     */
    public function acceptOrder(Request $request, $id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check verification status
        if (!$this->checkVerification()) {
            return response()->json([
                'success' => false,
                'error' => 'Your account is not verified. Please wait for admin approval.',
                'message' => 'Your account is not verified. Please wait for admin approval.'
            ], 403);
        }

        $establishmentId = Session::get('user_id');
        $order = Order::where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->whereIn('status', ['pending', 'pending_delivery_confirmation'])
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or cannot be accepted'], 404);
        }

        DB::beginTransaction();
        try {
            // Load food listing with relationship
            $order->load('foodListing');
            
            if (!$order->foodListing) {
                throw new \Exception('Food listing not found for this order');
            }
            
            // Deduct stock when accepting order (with availability check)
            $stockService = new \App\Services\StockService();
            $stockResult = $stockService->deductStock($order, $order->quantity);
            
            if (!$stockResult['success']) {
                // If stock is insufficient, return error
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => $stockResult['message'] ?? 'Insufficient stock available to accept this order',
                    'message' => $stockResult['message'] ?? 'Insufficient stock available to accept this order'
                ], 400);
            }

            // Update order status to accepted
            // For delivery orders, set to pending_delivery_confirmation (will be changed to on_the_way when marked out for delivery)
            // For pickup orders, set to accepted
            if ($order->delivery_method === 'delivery') {
                $order->status = 'pending_delivery_confirmation';
            } else {
                $order->status = 'accepted';
            }
            $order->accepted_at = now();
            $order->save();
            
            // Reload order with relationships for notification
            $order->load(['consumer', 'establishment', 'foodListing']);

            DB::commit();
            
            // Send notification to consumer
            NotificationService::notifyOrderAccepted($order);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to accept order: ' . $e->getMessage(),
                'message' => 'Failed to accept order: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order accepted successfully'
        ]);
    }

    /**
     * Cancel an order and restore quantity
     */
    public function cancelOrder(Request $request, $id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check verification status
        if (!$this->checkVerification()) {
            return response()->json([
                'success' => false,
                'error' => 'Your account is not verified. Please wait for admin approval.',
                'message' => 'Your account is not verified. Please wait for admin approval.'
            ], 403);
        }

        $establishmentId = Session::get('user_id');
        $order = Order::with('foodListing')
            ->where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or cannot be cancelled'], 404);
        }

        DB::beginTransaction();
        try {
            // Only restore stock if it was actually deducted (i.e., order was accepted)
            // If order is still pending, no stock was deducted, so nothing to restore
            if ($order->stock_deducted) {
                $stockService = new \App\Services\StockService();
                $stockResult = $stockService->restoreStock($order, $request->input('reason', 'Cancelled by establishment'));
                
                // Log the result but don't fail if stock wasn't deducted
                if (!$stockResult['success'] && strpos($stockResult['message'], 'already restored') === false) {
                    // Only throw if it's a real error, not if stock was never deducted
                    if (strpos($stockResult['message'], 'never deducted') === false) {
                        throw new \Exception($stockResult['message']);
                    }
                }
            }

            // Update order status
            $order->status = 'cancelled';
            $order->cancelled_at = now();
            $order->cancellation_reason = $request->input('reason', 'Cancelled by establishment');
            $order->save();
            
            // Reload order with relationships for notification
            $order->load(['consumer', 'establishment']);

            DB::commit();
            
            // Send notification to consumer
            NotificationService::notifyOrderCancelled($order, 'establishment');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel order: ' . $e->getMessage(),
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully' . ($order->stock_deducted ? ' and quantity restored' : '')
        ]);
    }

    /**
     * Mark order as complete
     */
    public function markOrderComplete(Request $request, $id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check verification status
        if (!$this->checkVerification()) {
            return response()->json([
                'success' => false,
                'error' => 'Your account is not verified. Please wait for admin approval.',
                'message' => 'Your account is not verified. Please wait for admin approval.'
            ], 403);
        }

        $establishmentId = Session::get('user_id');
        $order = Order::with('foodListing')
            ->where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->where('status', 'accepted')
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or cannot be completed'], 404);
        }

        // Prevent establishments from auto-completing delivery orders
        // Only consumers can confirm delivery
        if ($order->delivery_method === 'delivery') {
            return response()->json([
                'success' => false,
                'error' => 'Delivery orders can only be completed by the consumer when they confirm receipt. You cannot auto-complete delivery orders.',
                'message' => 'Delivery orders must be confirmed by the consumer.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Calculate 5% platform fee and net earnings
            $platformFeeRate = 0.05; // 5%
            $platformFee = round($order->total_price * $platformFeeRate, 2);
            $netEarnings = round($order->total_price - $platformFee, 2);
            
            // Stock is already deducted when order was accepted
            // Completing order just changes status - no stock movement needed
            $order->status = 'completed';
            $order->completed_at = now();
            $order->platform_fee = $platformFee;
            $order->net_earnings = $netEarnings;
            $order->save();
            
            // Reload order with relationships for notification
            $order->load(['consumer', 'establishment']);

            DB::commit();
            
            // Send notification to consumer
            NotificationService::notifyOrderCompleted($order);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to complete order: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order marked as complete successfully'
        ]);
    }

    /**
     * Mark order as out for delivery
     */
    public function markOutForDelivery(Request $request, $id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check verification status
        if (!$this->checkVerification()) {
            return response()->json([
                'success' => false,
                'error' => 'Your account is not verified. Please wait for admin approval.',
                'message' => 'Your account is not verified. Please wait for admin approval.'
            ], 403);
        }

        $establishmentId = Session::get('user_id');
        $order = Order::with(['foodListing', 'consumer', 'establishment'])
            ->where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->where('delivery_method', 'delivery')
            ->whereIn('status', ['pending_delivery_confirmation', 'accepted'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found or cannot be marked as out for delivery'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $order->status = 'on_the_way';
            $order->out_for_delivery_at = now();
            $order->save();

            // Reload order with relationships for notification
            $order->load(['consumer', 'establishment', 'foodListing']);

            DB::commit();

            // Send notification to consumer
            \App\Services\NotificationService::notifyOrderOutForDelivery($order);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark order as out for delivery: ' . $e->getMessage(),
                'message' => 'Failed to mark order as out for delivery: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order marked as out for delivery successfully'
        ]);
    }

    /**
     * Request admin intervention for delivery order
     */
    public function requestAdminIntervention(Request $request, $id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $establishmentId = Session::get('user_id');
        $order = Order::with(['foodListing', 'consumer', 'establishment'])
            ->where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->where('delivery_method', 'delivery')
            ->whereIn('status', ['on_the_way', 'pending_delivery_confirmation'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found or cannot request admin intervention'
            ], 404);
        }

        // Check if 24 hours have passed since out_for_delivery_at or accepted_at
        $checkTime = $order->out_for_delivery_at ?? $order->accepted_at;
        if (!$checkTime || now()->diffInHours($checkTime) < 24) {
            return response()->json([
                'success' => false,
                'error' => 'Admin intervention can only be requested after 24 hours from when the order was marked out for delivery or accepted.',
                'message' => 'Admin intervention can only be requested after 24 hours.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $order->admin_intervention_requested_at = now();
            $order->admin_intervention_reason = $request->reason;
            $order->save();

            // Reload order with relationships
            $order->load(['consumer', 'establishment', 'foodListing']);

            DB::commit();

            // Notify admin
            \App\Services\AdminNotificationService::notifyAdminInterventionRequested($order);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to request admin intervention: ' . $e->getMessage(),
                'message' => 'Failed to request admin intervention: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Admin intervention requested successfully'
        ]);
    }

    /**
     * Get order details for modal
     */
    public function getOrderDetails($id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $establishmentId = Session::get('user_id');
        $order = Order::with(['foodListing', 'consumer', 'establishment'])
            ->where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Format pickup times
        $pickupStartTime = null;
        $pickupEndTime = null;
        if ($order->pickup_start_time) {
            $pickupStartTime = is_string($order->pickup_start_time) 
                ? substr($order->pickup_start_time, 0, 5) 
                : $order->pickup_start_time->format('H:i');
        }
        if ($order->pickup_end_time) {
            $pickupEndTime = is_string($order->pickup_end_time) 
                ? substr($order->pickup_end_time, 0, 5) 
                : $order->pickup_end_time->format('H:i');
        }
        
        // Format pickup date (convert to Asia/Manila timezone)
        $pickupDate = $order->created_at ? \Carbon\Carbon::parse($order->created_at)->setTimezone('Asia/Manila')->format('F d, Y') : null;
        
        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'effective_status' => $order->effective_status,
                'is_missed_pickup' => $order->isMissedPickup(),
                'created_at' => \Carbon\Carbon::parse($order->created_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A'),
                'accepted_at' => $order->accepted_at ? \Carbon\Carbon::parse($order->accepted_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                'out_for_delivery_at' => $order->out_for_delivery_at ? \Carbon\Carbon::parse($order->out_for_delivery_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                'completed_at' => $order->completed_at ? \Carbon\Carbon::parse($order->completed_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                'cancelled_at' => $order->cancelled_at ? \Carbon\Carbon::parse($order->cancelled_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                'admin_intervention_requested_at' => $order->admin_intervention_requested_at ? \Carbon\Carbon::parse($order->admin_intervention_requested_at)->setTimezone('Asia/Manila')->format('F d, Y | h:i A') : null,
                'admin_intervention_reason' => $order->admin_intervention_reason,
                'payment_method' => ucfirst($order->payment_method ?? 'N/A'),
                'payment_status' => ucfirst($order->payment_status ?? 'N/A'),
                'delivery_method' => ucfirst($order->delivery_method),
                'customer_name' => urldecode($order->customer_name ?? ''),
                'customer_phone' => $order->customer_phone ?? 'N/A',
                'customer_email' => $order->consumer->email ?? 'N/A',
                'delivery_address' => $order->delivery_address ?? 'N/A',
                'delivery_lat' => $order->delivery_lat,
                'delivery_lng' => $order->delivery_lng,
                'delivery_distance' => $order->delivery_distance ? number_format($order->delivery_distance, 2) . ' km' : 'N/A',
                'delivery_fee' => $order->delivery_fee ? number_format($order->delivery_fee, 2) : ($order->delivery_method === 'delivery' ? '0.00' : '0.00'),
                'delivery_eta' => $order->delivery_eta ?? 'N/A',
                'delivery_instructions' => $order->delivery_instructions ?? 'None',
                'pickup_date' => $pickupDate,
                'pickup_start_time' => $pickupStartTime,
                'pickup_end_time' => $pickupEndTime,
                'pickup_time_range' => $pickupStartTime && $pickupEndTime 
                    ? $pickupStartTime . ' - ' . $pickupEndTime 
                    : ($pickupEndTime ? $pickupEndTime : null),
                'items' => [
                    [
                        'name' => $order->foodListing->name,
                        'quantity' => $order->quantity,
                        'unit_price' => (float) $order->unit_price,
                        'total_price' => (float) $order->total_price,
                    ]
                ],
                'subtotal' => (float) $order->total_price,
                'delivery_fee_amount' => 0.00,
                'total' => (float) $order->total_price,
                'store_name' => $order->establishment->business_name ?? ($order->establishment->owner_fname . ' ' . $order->establishment->owner_lname),
                'store_address' => $order->establishment->address ?? $order->foodListing->address ?? 'N/A',
            ]
        ]);
    }

    public function earnings()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        $establishmentId = Session::get('user_id');
        
        // Calculate total earnings from completed orders
        // Total gross earnings
        $totalGrossEarnings = Order::where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->sum('total_price');
        
        // Total platform fees
        $totalPlatformFees = Order::where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->sum('platform_fee');
        
        // Total net earnings
        $totalNetEarnings = Order::where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->sum('net_earnings');
        
        // If net_earnings is null for some orders, calculate it
        if ($totalNetEarnings == 0 && $totalGrossEarnings > 0) {
            // Backfill: calculate net earnings for orders without it
            $ordersWithoutNet = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereNull('net_earnings')
                ->get();
            
            foreach ($ordersWithoutNet as $order) {
                $platformFee = round($order->total_price * 0.05, 2);
                $netEarnings = round($order->total_price - $platformFee, 2);
                $order->platform_fee = $platformFee;
                $order->net_earnings = $netEarnings;
                $order->save();
            }
            
            // Recalculate totals
            $totalPlatformFees = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->sum('platform_fee');
            $totalNetEarnings = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->sum('net_earnings');
        }
        
        // Get completed orders for display
        $completedOrders = Order::with(['foodListing', 'consumer'])
            ->where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Calculate fee if not set
                $platformFee = $order->platform_fee ?? round($order->total_price * 0.05, 2);
                $netEarnings = $order->net_earnings ?? round($order->total_price - $platformFee, 2);
                
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'product_name' => $order->foodListing->name ?? 'Unknown Product',
                    'quantity' => $order->quantity,
                    'unit_price' => (float) $order->unit_price,
                    'total_price' => (float) $order->total_price,
                    'platform_fee' => (float) $platformFee,
                    'net_earnings' => (float) $netEarnings,
                    'customer_name' => $order->customer_name,
                    'payment_method' => $this->formatPaymentMethod($order->payment_method),
                    'completed_at' => $order->completed_at ? $order->completed_at->format('M d, Y') : null,
                    'created_at' => $order->created_at->format('M d, Y'),
                ];
            })
            ->toArray();

        // Calculate daily earnings (last 7 days) - using net earnings
        $dailyEarnings = [];
        // Map day of week (0=Sunday, 1=Monday, etc.) to labels
        $dayLabels = ['SUN', 'M', 'T', 'W', 'TH', 'FRI', 'SAT'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayOfWeek = $date->dayOfWeek; // 0=Sunday, 1=Monday, etc.
            $dayEarnings = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date->toDateString())
                ->sum('net_earnings');
            
            // If net_earnings is null, calculate from total_price
            if ($dayEarnings == 0) {
                $gross = Order::where('establishment_id', $establishmentId)
                    ->where('status', 'completed')
                    ->whereDate('completed_at', $date->toDateString())
                    ->sum('total_price');
                $dayEarnings = $gross * 0.95; // 95% after 5% fee
            }
            
            $dailyEarnings[] = [
                'label' => $dayLabels[$dayOfWeek],
                'value' => (float) $dayEarnings
            ];
        }

        // Calculate monthly earnings (last 12 months) - using net earnings
        $monthlyEarnings = [];
        $monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthEarnings = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->sum('net_earnings');
            
            // If net_earnings is null, calculate from total_price
            if ($monthEarnings == 0) {
                $gross = Order::where('establishment_id', $establishmentId)
                    ->where('status', 'completed')
                    ->whereYear('completed_at', $date->year)
                    ->whereMonth('completed_at', $date->month)
                    ->sum('total_price');
                $monthEarnings = $gross * 0.95;
            }
            
            $monthlyEarnings[] = [
                'label' => $monthLabels[$date->month - 1],
                'value' => (float) $monthEarnings
            ];
        }

        // Calculate yearly earnings (last 5 years) - using net earnings
        $yearlyEarnings = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            $yearEarnings = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $year)
                ->sum('net_earnings');
            
            // If net_earnings is null, calculate from total_price
            if ($yearEarnings == 0) {
                $gross = Order::where('establishment_id', $establishmentId)
                    ->where('status', 'completed')
                    ->whereYear('completed_at', $year)
                    ->sum('total_price');
                $yearEarnings = $gross * 0.95;
            }
            
            $yearlyEarnings[] = [
                'label' => (string) $year,
                'value' => (float) $yearEarnings
            ];
        }

        return view('establishment.earnings', compact(
            'user', 
            'totalGrossEarnings',
            'totalPlatformFees',
            'totalNetEarnings',
            'completedOrders', 
            'dailyEarnings', 
            'monthlyEarnings', 
            'yearlyEarnings'
        ));
    }

    /**
     * Export Establishment Earnings
     */
    public function exportEarnings(Request $request, $type)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }

        $establishmentId = Session::get('user_id');
        $user = $this->getUserData();

        // Get completed orders
        $orders = Order::with(['foodListing', 'consumer'])
            ->where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($order) {
                $platformFee = $order->platform_fee ?? round($order->total_price * 0.05, 2);
                $netEarnings = $order->net_earnings ?? round($order->total_price - $platformFee, 2);
                
                return [
                    'order_number' => $order->order_number,
                    'product_name' => $order->foodListing->name ?? 'Unknown Product',
                    'quantity' => $order->quantity,
                    'unit_price' => (float) $order->unit_price,
                    'total_price' => (float) $order->total_price,
                    'platform_fee' => (float) $platformFee,
                    'net_earnings' => (float) $netEarnings,
                    'customer_name' => $order->customer_name,
                    'payment_method' => $this->formatPaymentMethod($order->payment_method),
                    'completed_at' => $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : null,
                ];
            });

        switch ($type) {
            case 'csv':
                return $this->exportEarningsToCsv($orders, $user);
            case 'excel':
                return $this->exportEarningsToExcel($orders, $user);
            case 'pdf':
                return $this->exportEarningsToPdf($orders, $user);
            default:
                return redirect()->back()->with('error', 'Invalid export type.');
        }
    }

    /**
     * Export Earnings to CSV
     */
    private function exportEarningsToCsv($orders, $user)
    {
        $filename = 'earnings_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($orders, $user) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Order Number',
                'Item Sold',
                'Quantity',
                'Unit Price',
                'Gross Amount',
                'Platform Fee (5%)',
                'Net Earnings',
                'Customer Name',
                'Payment Method',
                'Date Completed'
            ]);

            // Data rows
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order['order_number'],
                    $order['product_name'],
                    $order['quantity'],
                    $order['unit_price'],
                    $order['total_price'],
                    $order['platform_fee'],
                    $order['net_earnings'],
                    $order['customer_name'],
                    $order['payment_method'],
                    $order['completed_at'] ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Earnings to Excel
     */
    private function exportEarningsToExcel($orders, $user)
    {
        $filename = 'earnings_' . date('Y-m-d_His') . '.xlsx';
        
        $data = [];
        
        // Headers
        $data[] = [
            'Order Number',
            'Item Sold',
            'Quantity',
            'Unit Price',
            'Gross Amount',
            'Platform Fee (5%)',
            'Net Earnings',
            'Customer Name',
            'Payment Method',
            'Date Completed'
        ];
        
        // Data rows
        foreach ($orders as $order) {
            $data[] = [
                $order['order_number'],
                $order['product_name'],
                $order['quantity'],
                $order['unit_price'],
                $order['total_price'],
                $order['platform_fee'],
                $order['net_earnings'],
                $order['customer_name'],
                $order['payment_method'],
                $order['completed_at'] ?? 'N/A'
            ];
        }
        
        return Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Earnings', function($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
                
                // Style the header row
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                    $row->setBackground('#2d5016');
                    $row->setFontColor('#ffffff');
                });
                
                // Auto-size columns
                foreach(range('A', 'J') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            });
        })->export('xlsx');
    }

    /**
     * Export Earnings to PDF
     */
    private function exportEarningsToPdf($orders, $user)
    {
        $establishmentName = $user->business_name ?? 'Establishment';
        $totalGross = $orders->sum('total_price');
        $totalFees = $orders->sum('platform_fee');
        $totalNet = $orders->sum('net_earnings');
        
        $data = $orders->map(function ($order) {
            return [
                'order_number' => $order['order_number'],
                'product_name' => $order['product_name'],
                'quantity' => $order['quantity'],
                'unit_price' => $order['unit_price'],
                'total_price' => $order['total_price'],
                'platform_fee' => $order['platform_fee'],
                'net_earnings' => $order['net_earnings'],
                'customer_name' => $order['customer_name'],
                'payment_method' => $order['payment_method'],
                'completed_at' => $order['completed_at'] ?? 'N/A',
            ];
        })->toArray();

        $filename = 'earnings_' . date('Y-m-d_His') . '.pdf';
        
        $pdf = Pdf::loadView('establishment.earnings-pdf', [
            'data' => $data,
            'establishmentName' => $establishmentName,
            'totalGross' => $totalGross,
            'totalFees' => $totalFees,
            'totalNet' => $totalNet,
            'exportDate' => now()->format('F d, Y'),
            'totalOrders' => count($data)
        ]);
        
        return $pdf->download($filename);
    }

    public function donationHub()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        $establishmentId = Session::get('user_id');
        $establishment = Establishment::find($establishmentId);
        $isVerified = $establishment && $establishment->isVerified();
        
        // Fetch all active donation requests from food banks
        // Exclude requests that have been accepted (establishment-submitted requests that are accepted should not appear)
        $establishmentId = Session::get('user_id');
        $donationRequests = DonationRequest::where(function($query) use ($establishmentId) {
                // Only show requests that are:
                // 1. Foodbank-initiated requests (whereNull('establishment_id')) that are pending, OR
                // 2. Establishment-submitted requests that are still pending (not yet accepted)
                $query->where(function($q) {
                    // Foodbank-initiated requests - only show pending ones
                    $q->whereNull('establishment_id')
                      ->whereIn('status', [
                          DonationRequestService::STATUS_PENDING,
                          DonationRequestService::STATUS_PENDING_CONFIRMATION
                      ]);
                })->orWhere(function($q) use ($establishmentId) {
                    // Establishment-submitted requests - only show pending ones (exclude accepted)
                    $q->where('establishment_id', $establishmentId)
                      ->whereIn('status', [
                          DonationRequestService::STATUS_PENDING,
                          DonationRequestService::STATUS_PENDING_CONFIRMATION
                      ]);
                });
            })
            ->whereDoesntHave('donation') // Exclude requests that have been fulfilled (have a Donation record)
            ->with('foodbank')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                $foodbank = $request->foodbank;
                
                // Format time display
                $timeDisplay = 'N/A';
                if ($request->time_option === 'specific' && $request->start_time && $request->end_time) {
                    $startTime = is_string($request->start_time) ? substr($request->start_time, 0, 5) : $request->start_time->format('H:i');
                    $endTime = is_string($request->end_time) ? substr($request->end_time, 0, 5) : $request->end_time->format('H:i');
                    $timeDisplay = $startTime . ' - ' . $endTime;
                } elseif ($request->time_option === 'anytime') {
                    $timeDisplay = 'Anytime';
                } elseif ($request->time_option === 'allDay') {
                    $timeDisplay = 'All Day';
                }
                
                return [
                    'id' => $request->donation_request_id,
                    'foodbank_id' => $request->foodbank_id,
                    'establishment_id' => $request->establishment_id, // Track if this is establishment-submitted
                    'foodbank_name' => $foodbank->organization_name ?? 'Food Bank',
                    'foodbank_profile_image' => $foodbank->profile_image ?? null,
                    'item_name' => $request->item_name,
                    'quantity' => $request->quantity,
                    'category' => $request->category,
                    'description' => $request->description,
                    'distribution_zone' => $request->distribution_zone,
                    'dropoff_date' => $request->dropoff_date->format('Y-m-d'),
                    'dropoff_date_display' => $request->dropoff_date->format('F d, Y'),
                    'time_option' => $request->time_option,
                    'time_display' => $timeDisplay,
                    'start_time' => $request->start_time ? (is_string($request->start_time) ? substr($request->start_time, 0, 5) : $request->start_time->format('H:i')) : null,
                    'end_time' => $request->end_time ? (is_string($request->end_time) ? substr($request->end_time, 0, 5) : $request->end_time->format('H:i')) : null,
                    'address' => $request->address,
                    'contact_name' => $request->contact_name,
                    'phone_number' => $request->phone_number,
                    'email' => $request->email,
                    'status' => $request->status,
                    'status_display' => ucfirst($request->status),
                    'matches' => $request->matches,
                    'created_at' => $request->created_at->format('F d, Y'),
                ];
            })
            ->toArray();
        
        // Fetch all registered food banks
        $foodbanks = Foodbank::orderBy('organization_name', 'asc')
            ->get()
            ->map(function ($foodbank) {
                return [
                    'id' => $foodbank->foodbank_id,
                    'organization_name' => $foodbank->organization_name,
                    'address' => $foodbank->address ?? 'Not provided',
                    'phone_no' => $foodbank->phone_no ?? 'Not provided',
                    'email' => $foodbank->email,
                    'contact_person' => $foodbank->contact_person ?? 'Not provided',
                    'registration_number' => $foodbank->registration_number ?? 'Not provided',
                    'profile_image' => $foodbank->profile_image ?? null,
                ];
            })
            ->toArray();
        
        return view('establishment.donation-hub', compact(
            'user',
            'donationRequests',
            'isVerified',
            'foodbanks',
            'establishment'
        ));
    }

    /**
     * Check status of donation requests (for real-time updates)
     */
    public function checkRequestStatus(Request $request)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.'
            ], 403);
        }

        $establishmentId = Session::get('user_id');
        $requestIds = $request->input('request_ids', []);

        if (empty($requestIds) || !is_array($requestIds)) {
            return response()->json([
                'success' => true,
                'accepted_requests' => []
            ]);
        }

        // Check which requests have been accepted or have a linked donation
        $acceptedRequests = DonationRequest::whereIn('donation_request_id', $requestIds)
            ->where(function($query) use ($establishmentId, $requestIds) {
                // Requests submitted by this establishment that are accepted
                $query->where(function($q) use ($establishmentId) {
                    $q->where('establishment_id', $establishmentId)
                      ->where('status', DonationRequestService::STATUS_ACCEPTED);
                })
                // Or requests that have been fulfilled (have a Donation record)
                ->orWhereHas('donation');
            })
            ->pluck('donation_request_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'accepted_requests' => $acceptedRequests
        ]);
    }

    /**
     * Fulfill a donation request (when establishment donates to fulfill a foodbank's request)
     */
    public function fulfillDonationRequest(Request $request, $requestId)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as an establishment.'
            ], 403);
        }

        // Check verification status
        if (!$this->checkVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not verified. Please wait for admin approval.',
                'error' => 'unverified_account'
            ], 403);
        }

        $establishmentId = Session::get('user_id');

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $requestId)
                ->whereIn('status', [
                    DonationRequestService::STATUS_PENDING,
                    DonationRequestService::STATUS_ACCEPTED
                ])
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found or already fulfilled.'
                ], 404);
            }

            // Validate the donation details (pickup-only)
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:20',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'pickup_method' => 'nullable|in:pickup', // Always pickup, but accept if sent
            'establishment_notes' => 'nullable|string',
        ]);

            // Set default scheduled_date to today if not provided
            $scheduledDate = $request->input('scheduled_date') ? \Carbon\Carbon::parse($request->input('scheduled_date')) : now();
            
            // Create the donation linked to this request
            $donation = Donation::create([
                'foodbank_id' => $donationRequest->foodbank_id,
                'establishment_id' => $establishmentId,
                'donation_request_id' => $donationRequest->donation_request_id,
                'item_name' => $validated['item_name'],
                'item_category' => $validated['category'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'] ?? 'pcs',
                'description' => $validated['description'] ?? null,
                'expiry_date' => null, // Not collected in pickup-only form
                'status' => 'pending_pickup',
                'pickup_method' => 'pickup', // Always pickup
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => null, // Not collected in pickup-only form
                'establishment_notes' => $validated['establishment_notes'] ?? null,
                'is_urgent' => false,
                'is_nearing_expiry' => false,
            ]);

            // Update the donation request - set to 'active' (pending foodbank confirmation)
            // Status will be set to 'completed' only after foodbank confirms pickup/delivery
            $donationRequest->fulfilled_by_establishment_id = $establishmentId;
            $donationRequest->donation_id = $donation->donation_id;
            $donationRequest->status = 'active'; // Active means fulfilled but awaiting confirmation
            $donationRequest->matches = ($donationRequest->matches ?? 0) + 1;
            // Don't set fulfilled_at until foodbank confirms
            $donationRequest->save();

            // Reload donation with relationships for notification
            $donation->load(['foodbank', 'establishment']);
            
            // Send notification to foodbank
            NotificationService::notifyDonationCreated($donation);

            // Dispatch event for automatic logging
            \App\Events\DonationOfferSubmitted::dispatch($donation, $establishmentId, 'establishment');

            return response()->json([
                'success' => true,
                'message' => 'Donation request fulfilled successfully! The foodbank will be notified.',
                'data' => [
                    'id' => $donation->donation_id,
                    'donation_number' => $donation->donation_number,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fulfill donation request. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get foodbank contact details
     */
    public function getFoodbankContact($foodbankId)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as an establishment.'
            ], 403);
        }

        try {
            $foodbank = Foodbank::where('foodbank_id', $foodbankId)->first();

            if (!$foodbank) {
                return response()->json([
                    'success' => false,
                    'message' => 'Foodbank not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $foodbank->foodbank_id,
                    'organization_name' => $foodbank->organization_name,
                    'email' => $foodbank->email,
                    'phone_no' => $foodbank->phone_no ?? 'Not provided',
                    'address' => $foodbank->address ?? 'Not provided',
                    'contact_person' => $foodbank->contact_person ?? 'Not provided',
                    'registration_number' => $foodbank->registration_number ?? 'Not provided',
                    'is_verified' => $foodbank->is_verified ?? false,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve foodbank details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a donation request from establishment
     */
    public function storeDonationRequest(Request $request)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            \Log::warning('storeDonationRequest: Access denied', [
                'has_user' => Session::has('user_id'),
                'user_type' => Session::get('user_type'),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as an establishment.'
            ], 403);
        }

        // Check verification status
        if (!$this->checkVerification()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not verified. Please wait for admin approval.',
                'error' => 'unverified_account'
            ], 403);
        }

        $establishmentId = Session::get('user_id');

        // Validate the request
        $validated = $request->validate([
            'foodbank_id' => 'required|uuid|exists:foodbanks,foodbank_id',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:20',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'pickup_method' => 'nullable|in:pickup', // Always pickup, but accept it if sent
            'establishment_notes' => 'nullable|string',
        ]);

        try {
            // Get establishment data for required fields
            $establishment = Establishment::find($establishmentId);
            if (!$establishment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Establishment not found.'
                ], 404);
            }
            
            // Ensure foodbank_id is a string (UUID)
            $foodbankId = $validated['foodbank_id'];
            if (!is_string($foodbankId)) {
                $foodbankId = (string) $foodbankId;
            }
            
            // Check for duplicate active donation request
            // Same establishment, same foodbank, same item (case-insensitive), with active status (pending_confirmation or accepted)
            $existingRequest = DonationRequest::where('establishment_id', $establishmentId)
                ->where('foodbank_id', $foodbankId)
                ->whereRaw('LOWER(item_name) = LOWER(?)', [$validated['item_name']])
                ->whereIn('status', [
                    DonationRequestService::STATUS_PENDING_CONFIRMATION,
                    DonationRequestService::STATUS_ACCEPTED
                ])
                ->first();
            
            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active donation request for this item with this food bank.'
                ], 409); // 409 Conflict status code
            }
            
            // Prepare contact information from establishment
            $contactName = trim(($establishment->owner_fname ?? '') . ' ' . ($establishment->owner_lname ?? ''));
            if (empty($contactName)) {
                $contactName = $establishment->business_name ?? 'Establishment Contact';
            }
            
            // Set default scheduled_date to today if not provided
            $scheduledDate = $request->input('scheduled_date') ? \Carbon\Carbon::parse($request->input('scheduled_date')) : now();
            $dropoffDate = $scheduledDate;
            
            // Create the donation request (from establishment to foodbank)
            // Include required fields that aren't in the form but are needed by the database
            try {
                $donationRequest = DonationRequest::create([
                    'foodbank_id' => $foodbankId,
                    'establishment_id' => $establishmentId,
                    'item_name' => $validated['item_name'],
                    'quantity' => $validated['quantity'],
                    'unit' => $validated['unit'] ?? 'pcs',
                    'category' => $validated['category'],
                    'description' => $validated['description'] ?? null,
                    'expiry_date' => null, // Not collected in pickup-only form
                    'scheduled_date' => $scheduledDate,
                    'scheduled_time' => null, // Not collected in pickup-only form
                    'pickup_method' => 'pickup', // Always pickup
                    'establishment_notes' => $validated['establishment_notes'] ?? null,
                    'status' => DonationRequestService::STATUS_PENDING_CONFIRMATION,
                    // Required fields from original schema (not collected in establishment form)
                    'distribution_zone' => 'N/A', // Not used for pickup-only requests
                    'dropoff_date' => $dropoffDate, // Use scheduled_date as dropoff_date
                    'address' => $establishment->formatted_address ?? $establishment->address ?? 'Address not provided', // Use establishment's registered address
                    'contact_name' => $contactName,
                    'phone_number' => $establishment->phone_no ?? 'Not provided',
                    'email' => $establishment->email ?? 'notprovided@example.com',
                    'time_option' => 'anytime', // Default for establishment requests
                ]);
                
                
            } catch (\Illuminate\Database\QueryException $e) {
                \Log::error('Database error creating donation request', [
                    'error' => $e->getMessage(),
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                ]);
                throw $e;
            } catch (\Exception $e) {
                \Log::error('Unexpected error creating donation request', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
            
            // Reload donation request with relationships for notification
            $donationRequest->load(['foodbank', 'establishment']);
            
            // Send notification to foodbank (wrap in try-catch to prevent blocking)
            try {
                NotificationService::notifyDonationRequested($donationRequest);
            } catch (\Exception $e) {
                \Log::error('Error sending notification for donation request', [
                    'donation_request_id' => $donationRequest->donation_request_id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Dispatch event for automatic logging (wrap in try-catch to prevent blocking)
            try {
                \App\Events\DonationRequestCreated::dispatch($donationRequest, $establishmentId, 'establishment');
            } catch (\Exception $e) {
                \Log::error('Error dispatching DonationRequestCreated event', [
                    'donation_request_id' => $donationRequest->donation_request_id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Donation request submitted successfully! The foodbank will review your request.',
                'data' => [
                    'id' => $donationRequest->donation_request_id,
                    'foodbank_id' => $donationRequest->foodbank_id,
                    'status' => $donationRequest->status,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for donation request', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Exception in storeDonationRequest', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            \Log::error('Donation request submission error: ' . $e->getMessage(), [
                'establishment_id' => $establishmentId,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit donation request. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred. Please contact support.'
            ], 500);
        }
    }

    /**
     * Display establishment's donation requests organized by status
     */
    public function myDonationRequests()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        $establishmentId = Session::get('user_id');
        $establishment = Establishment::find($establishmentId);
        $isVerified = $establishment && $establishment->isVerified();
        
        // Fetch PENDING requests (including pending_confirmation)
        $pendingRequests = DonationRequest::where('establishment_id', $establishmentId)
            ->whereIn('status', [
                \App\Services\DonationRequestService::STATUS_PENDING,
                \App\Services\DonationRequestService::STATUS_PENDING_CONFIRMATION
            ])
            ->with(['foodbank'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($request) {
                return \App\Services\DonationRequestService::formatRequestData($request);
            })
            ->toArray();
        
        // Fetch ACCEPTED requests
        $acceptedRequests = DonationRequest::where('establishment_id', $establishmentId)
            ->where('status', \App\Services\DonationRequestService::STATUS_ACCEPTED)
            ->with(['foodbank'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($request) {
                return \App\Services\DonationRequestService::formatRequestData($request);
            })
            ->toArray();
        
        // Fetch DECLINED requests
        $declinedRequests = DonationRequest::where('establishment_id', $establishmentId)
            ->where('status', \App\Services\DonationRequestService::STATUS_DECLINED)
            ->with(['foodbank'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function($request) {
                return \App\Services\DonationRequestService::formatRequestData($request);
            })
            ->toArray();
        
        // Fetch COMPLETED requests
        $completedRequests = DonationRequest::where('establishment_id', $establishmentId)
            ->where('status', \App\Services\DonationRequestService::STATUS_COMPLETED)
            ->with(['foodbank', 'donation'])
            ->orderBy('fulfilled_at', 'desc')
            ->get()
            ->map(function($request) {
                return \App\Services\DonationRequestService::formatRequestData($request);
            })
            ->toArray();
        
        return view('establishment.my-donation-requests', compact(
            'user',
            'pendingRequests',
            'acceptedRequests',
            'declinedRequests',
            'completedRequests'
        ));
    }

    /**
     * Get donation request details for establishment
     */
    public function getDonationRequestDetails($id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as an establishment.'
            ], 403);
        }

        $establishmentId = Session::get('user_id');

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $id)
                ->where('establishment_id', $establishmentId)
                ->with(['foodbank', 'donation'])
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => \App\Services\DonationRequestService::formatRequestData($donationRequest)
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching donation request details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch donation request details.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred.'
            ], 500);
        }
    }

    public function impactReports()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        $establishmentId = Session::get('user_id');
        $establishment = Establishment::find($establishmentId);
        $isVerified = $establishment && $establishment->isVerified();
        
        // Get all completed orders for this establishment
        $completedOrders = Order::with('foodListing')
            ->where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->get();
        
        // Calculate Food Saved: total quantity from completed orders
        $foodSaved = $completedOrders->sum('quantity');
        
        // Calculate Cost Savings: total earnings from completed orders
        $costSavings = Order::where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->sum('total_price');
        $costSavings = round($costSavings, 2);
        
        // Calculate Food Donated: number of items from completed/collected donations
        $foodDonated = Donation::where('establishment_id', $establishmentId)
            ->whereIn('status', ['collected'])
            ->count();
        
        // Calculate chart data: Daily, Monthly, and Yearly food saved
        $dailyData = [];
        $monthlyData = [];
        $yearlyData = [];
        
        // Daily data (last 7 days)
        $dayLabels = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayOfWeek = $date->dayOfWeek;
            
            $dayFoodSaved = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date->toDateString())
                ->sum('quantity');
            
            $dailyData[] = [
                'label' => $dayLabels[$dayOfWeek],
                'value' => (int) $dayFoodSaved
            ];
        }
        
        // Monthly data (last 12 months)
        $monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            $monthFoodSaved = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->sum('quantity');
            
            $monthlyData[] = [
                'label' => $monthLabels[$date->month - 1],
                'value' => (int) $monthFoodSaved
            ];
        }
        
        // Yearly data (last 5 years)
        for ($i = 4; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            
            $yearFoodSaved = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $year)
                ->sum('quantity');
            
            $yearlyData[] = [
                'label' => (string) $year,
                'value' => (int) $yearFoodSaved
            ];
        }
        
        // Calculate foodbanks ranking by donated items (only completed/collected donations)
        $topDonatedItems = Donation::where('establishment_id', $establishmentId)
            ->where('status', 'collected')
            ->with('foodbank')
            ->get()
            ->groupBy('foodbank_id')
            ->map(function ($donations) {
                $foodbank = $donations->first()->foodbank;
                $totalQuantity = $donations->sum('quantity');
                return [
                    'foodbank_name' => $foodbank->organization_name ?? 'Unknown Foodbank',
                    'quantity' => (int) $totalQuantity
                ];
            })
            ->sortByDesc('quantity')
            ->take(5)
            ->values()
            ->toArray();
        
        // Calculate total for percentage calculation
        $totalDonated = array_sum(array_column($topDonatedItems, 'quantity'));
        
        // Add percentages to foodbanks ranking
        foreach ($topDonatedItems as &$item) {
            $item['percentage'] = $totalDonated > 0 ? round(($item['quantity'] / $totalDonated) * 100, 2) : 0;
        }
        
        return view('establishment.impact-reports', compact(
            'user',
            'foodSaved',
            'costSavings',
            'foodDonated',
            'dailyData',
            'monthlyData',
            'yearlyData',
            'topDonatedItems'
        ));
    }

    /**
     * Export Impact Reports
     */
    public function exportImpactReports(Request $request, $format)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $establishmentId = Session::get('user_id');
        $user = $this->getUserData();
        
        // Get all the same data as impactReports
        $completedOrders = Order::with('foodListing')
            ->where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->get();
        
        $foodSaved = $completedOrders->sum('quantity');
        $costSavings = Order::where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->sum('total_price');
        $costSavings = round($costSavings, 2);
        
        $foodDonated = Donation::where('establishment_id', $establishmentId)
            ->whereIn('status', ['collected'])
            ->count();
        
        // Get chart data
        $dailyData = [];
        $monthlyData = [];
        $yearlyData = [];
        
        $dayLabels = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayOfWeek = $date->dayOfWeek;
            $dayFoodSaved = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date->toDateString())
                ->sum('quantity');
            $dailyData[] = [
                'label' => $dayLabels[$dayOfWeek],
                'value' => (int) $dayFoodSaved
            ];
        }
        
        $monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthFoodSaved = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->sum('quantity');
            $monthlyData[] = [
                'label' => $monthLabels[$date->month - 1],
                'value' => (int) $monthFoodSaved
            ];
        }
        
        for ($i = 4; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            $yearFoodSaved = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $year)
                ->sum('quantity');
            $yearlyData[] = [
                'label' => (string) $year,
                'value' => (int) $yearFoodSaved
            ];
        }
        
        $topDonatedItems = Donation::where('establishment_id', $establishmentId)
            ->where('status', 'collected')
            ->with('foodbank')
            ->get()
            ->groupBy('foodbank_id')
            ->map(function ($donations) {
                $foodbank = $donations->first()->foodbank;
                $totalQuantity = $donations->sum('quantity');
                return [
                    'foodbank_name' => $foodbank->organization_name ?? 'Unknown Foodbank',
                    'quantity' => (int) $totalQuantity
                ];
            })
            ->sortByDesc('quantity')
            ->take(5)
            ->values()
            ->toArray();
        
        $totalDonated = array_sum(array_column($topDonatedItems, 'quantity'));
        foreach ($topDonatedItems as &$item) {
            $item['percentage'] = $totalDonated > 0 ? round(($item['quantity'] / $totalDonated) * 100, 2) : 0;
        }
        
        $establishmentName = $user->business_name ?? 'Establishment';
        $reportDate = now()->format('F j, Y');
        $dateRange = 'All Time';
        
        switch ($format) {
            case 'pdf':
                return $this->exportImpactReportsToPdf(
                    $establishmentName,
                    $reportDate,
                    $dateRange,
                    $foodSaved,
                    $costSavings,
                    $foodDonated,
                    $dailyData,
                    $monthlyData,
                    $yearlyData,
                    $topDonatedItems
                );
            case 'csv':
                return $this->exportImpactReportsToCsv(
                    $establishmentName,
                    $reportDate,
                    $dateRange,
                    $foodSaved,
                    $costSavings,
                    $foodDonated,
                    $dailyData,
                    $monthlyData,
                    $yearlyData,
                    $topDonatedItems
                );
            default:
                return redirect()->back()->with('error', 'Invalid export format.');
        }
    }

    /**
     * Export Impact Reports to PDF
     */
    private function exportImpactReportsToPdf($establishmentName, $reportDate, $dateRange, $foodSaved, $costSavings, $foodDonated, $dailyData, $monthlyData, $yearlyData, $topDonatedItems)
    {
        $data = compact(
            'establishmentName',
            'reportDate',
            'dateRange',
            'foodSaved',
            'costSavings',
            'foodDonated',
            'dailyData',
            'monthlyData',
            'yearlyData',
            'topDonatedItems'
        );
        
        $pdf = Pdf::loadView('establishment.exports.impact-reports-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        
        $filename = 'impact_report_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export Impact Reports to CSV
     */
    private function exportImpactReportsToCsv($establishmentName, $reportDate, $dateRange, $foodSaved, $costSavings, $foodDonated, $dailyData, $monthlyData, $yearlyData, $topDonatedItems)
    {
        $filename = 'impact_report_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($establishmentName, $reportDate, $dateRange, $foodSaved, $costSavings, $foodDonated, $dailyData, $monthlyData, $yearlyData, $topDonatedItems) {
            $file = fopen('php://output', 'w');
            
            // Header Section
            fputcsv($file, ['Impact Report - ' . $establishmentName]);
            fputcsv($file, ['Generated: ' . $reportDate]);
            fputcsv($file, ['Date Range: ' . $dateRange]);
            fputcsv($file, []); // Empty row
            
            // Summary Section
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, ['Food Saved', $foodSaved]);
            fputcsv($file, ['Cost Savings', '₱' . number_format($costSavings, 2)]);
            fputcsv($file, ['Food Donations Completed', $foodDonated]);
            fputcsv($file, []); // Empty row
            
            // Monthly Data
            fputcsv($file, ['MONTHLY TRENDS']);
            fputcsv($file, ['Month', 'Items Saved']);
            foreach ($monthlyData as $data) {
                fputcsv($file, [$data['label'], $data['value']]);
            }
            fputcsv($file, []); // Empty row
            
            // Daily Data
            fputcsv($file, ['DAILY TRENDS (Last 7 Days)']);
            fputcsv($file, ['Day', 'Items Saved']);
            foreach ($dailyData as $data) {
                fputcsv($file, [$data['label'], $data['value']]);
            }
            fputcsv($file, []); // Empty row
            
            // Yearly Data
            fputcsv($file, ['YEARLY TRENDS']);
            fputcsv($file, ['Year', 'Items Saved']);
            foreach ($yearlyData as $data) {
                fputcsv($file, [$data['label'], $data['value']]);
            }
            fputcsv($file, []); // Empty row
            
            // Top Donated Items
            fputcsv($file, ['FOODBANKS RANKING OF DONATED ITEMS']);
            fputcsv($file, ['Foodbank Name', 'Quantity', 'Percentage']);
            foreach ($topDonatedItems as $item) {
                fputcsv($file, [
                    $item['foodbank_name'],
                    $item['quantity'],
                    $item['percentage'] . '%'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function settings()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $establishmentId = Session::get('user_id');
        $userData = Establishment::find($establishmentId);
        $isVerified = $userData && $userData->isVerified();
        
        if (!$userData) {
            return redirect()->route('login')->with('error', 'Establishment not found.');
        }

        return view('establishment.settings', compact('userData'));
    }

    public function help()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $establishmentId = Session::get('user_id');
        $establishment = Establishment::find($establishmentId);
        $isVerified = $establishment && $establishment->isVerified();

        return view('establishment.help');
    }

    /**
     * Store a new food listing
     */
    public function storeFoodListing(Request $request)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check verification status
        if (!$this->checkVerification()) {
            return response()->json([
                'success' => false,
                'error' => 'Your account is not verified. Please wait for admin approval.',
                'message' => 'Your account is not verified. Please wait for admin approval.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:fruits-vegetables,baked-goods,cooked-meals,packaged-goods,beverages',
            'quantity' => 'required|integer|min:1',
            'original_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'expiry_date' => 'required|date|after_or_equal:today',
            'address' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $establishmentId = Session::get('user_id');
            $data = $request->all();
            
            // Calculate discounted price
            $discountedPrice = null;
            if ($data['discount_percentage'] && $data['discount_percentage'] > 0) {
                $discountAmount = ($data['original_price'] * $data['discount_percentage']) / 100;
                $discountedPrice = $data['original_price'] - $discountAmount;
            }

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                try {
                    $imagePath = $request->file('image')->store('food-listings', 'public');
                    
                    // Verify the file was actually stored
                    if (!Storage::disk('public')->exists($imagePath)) {
                        \Log::error('Image upload failed: File not found after storage', ['path' => $imagePath]);
                        $imagePath = null;
                    }
                } catch (\Exception $e) {
                    \Log::error('Image upload failed: ' . $e->getMessage());
                    $imagePath = null;
                }
            }

            $foodListing = FoodListing::create([
                'establishment_id' => $establishmentId,
                'name' => $data['name'],
                'description' => $data['description'],
                'category' => $data['category'],
                'quantity' => $data['quantity'],
                'original_price' => $data['original_price'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discounted_price' => $discountedPrice,
                'expiry_date' => $data['expiry_date'],
                'address' => $data['address'],
                'image_path' => $imagePath,
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Food listing created successfully',
                'data' => $foodListing
            ]);

        } catch (\Exception $e) {
            \Log::error('Food listing creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create food listing',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing food listing
     */
    public function updateFoodListing(Request $request, $id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check verification status
        if (!$this->checkVerification()) {
            return response()->json([
                'success' => false,
                'error' => 'Your account is not verified. Please wait for admin approval.',
                'message' => 'Your account is not verified. Please wait for admin approval.'
            ], 403);
        }

        $establishmentId = Session::get('user_id');
        $foodListing = FoodListing::where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->first();

        if (!$foodListing) {
            return response()->json(['error' => 'Food listing not found'], 404);
        }


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:fruits-vegetables,baked-goods,cooked-meals,packaged-goods,beverages',
            'quantity' => 'required|integer|min:1',
            'original_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'expiry_date' => 'required|date|after_or_equal:today',
            'address' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            
            // Calculate discounted price
            $discountedPrice = null;
            if ($data['discount_percentage'] && $data['discount_percentage'] > 0) {
                $discountAmount = ($data['original_price'] * $data['discount_percentage']) / 100;
                $discountedPrice = $data['original_price'] - $discountAmount;
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                try {
                    // Delete old image if exists
                    if ($foodListing->image_path) {
                        Storage::disk('public')->delete($foodListing->image_path);
                    }
                    
                    $imagePath = $request->file('image')->store('food-listings', 'public');
                    
                    // Verify the file was actually stored
                    if (Storage::disk('public')->exists($imagePath)) {
                        $data['image_path'] = $imagePath;
                    } else {
                        \Log::error('Image upload failed: File not found after storage', ['path' => $imagePath]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Image upload failed: ' . $e->getMessage());
                }
            }

            // Prepare update data - exclude status to prevent establishments from changing it
            $updateData = [
                'name' => $data['name'],
                'description' => $data['description'],
                'category' => $data['category'],
                'quantity' => $data['quantity'],
                'original_price' => $data['original_price'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discounted_price' => $discountedPrice,
                'expiry_date' => $data['expiry_date'],
                'address' => $data['address'],
                'image_path' => $data['image_path'] ?? $foodListing->image_path,
            ];
            
            // Note: Status is intentionally excluded - only admins can change status
            // Establishments can edit all other fields even when disabled
            
            $foodListing->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Food listing updated successfully',
                'data' => $foodListing
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update food listing'], 500);
        }
    }

    /**
     * Delete a food listing
     */
    public function deleteFoodListing($id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check verification status
        if (!$this->checkVerification()) {
            return response()->json([
                'success' => false,
                'error' => 'Your account is not verified. Please wait for admin approval.',
                'message' => 'Your account is not verified. Please wait for admin approval.'
            ], 403);
        }

        $establishmentId = Session::get('user_id');
        $foodListing = FoodListing::where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->first();

        if (!$foodListing) {
            return response()->json(['error' => 'Food listing not found'], 404);
        }

        try {
            // Delete image if exists
            if ($foodListing->image_path) {
                Storage::disk('public')->delete($foodListing->image_path);
            }

            $foodListing->delete();

            return response()->json([
                'success' => true,
                'message' => 'Food listing deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete food listing'], 500);
        }
    }

    /**
     * Show donation history page for establishment
     */
    public function donationHistory(Request $request)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        $establishmentId = Session::get('user_id');
        $establishment = Establishment::find($establishmentId);
        $isVerified = $establishment && $establishment->isVerified();
        
        // Get Donation records (fulfilled donations)
        $donationQuery = Donation::where('establishment_id', $establishmentId)
            ->with(['foodbank', 'donationRequest']);

        // Get DonationRequest records (accepted, declined, completed requests)
        $requestQuery = DonationRequest::where('establishment_id', $establishmentId)
            ->whereIn('status', [
                \App\Services\DonationRequestService::STATUS_ACCEPTED,
                \App\Services\DonationRequestService::STATUS_DECLINED,
                \App\Services\DonationRequestService::STATUS_COMPLETED
            ])
            ->with(['foodbank', 'donation']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $donationQuery->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhereHas('foodbank', function($q) use ($search) {
                      $q->where('organization_name', 'like', "%{$search}%");
                  });
            });
            $requestQuery->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhereHas('foodbank', function($q) use ($search) {
                      $q->where('organization_name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->status;
            // Check if it's a donation status or request status
            $donationStatuses = ['pending_pickup', 'ready_for_collection', 'collected', 'cancelled', 'expired'];
            $requestStatuses = ['accepted', 'declined', 'completed'];
            
            if (in_array($status, $donationStatuses)) {
                $donationQuery->where('status', $status);
            } elseif (in_array($status, $requestStatuses)) {
                $requestQuery->where('status', $status);
            }
        }

        // Apply date filters
        if ($request->filled('date_from')) {
            $donationQuery->where('created_at', '>=', $request->date_from);
            $requestQuery->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $donationQuery->where('created_at', '<=', $request->date_to . ' 23:59:59');
            $requestQuery->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Get all donations and requests for statistics
        $allDonations = Donation::where('establishment_id', $establishmentId)->get();
        $allRequests = DonationRequest::where('establishment_id', $establishmentId)
            ->whereIn('status', [
                \App\Services\DonationRequestService::STATUS_ACCEPTED,
                \App\Services\DonationRequestService::STATUS_DECLINED,
                \App\Services\DonationRequestService::STATUS_COMPLETED
            ])
            ->get();

        // Format Donation records
        $formattedDonations = $donationQuery->get()->map(function ($donation) {
            $foodbank = $donation->foodbank;
            
            return [
                'id' => $donation->donation_id,
                'type' => 'donation',
                'donation_number' => $donation->donation_number,
                'item_name' => $donation->item_name,
                'category' => $donation->item_category,
                'quantity' => $donation->quantity,
                'unit' => $donation->unit,
                'date_donated' => $donation->created_at->format('F d, Y'),
                'date_donated_raw' => $donation->created_at->format('Y-m-d'),
                'foodbank_name' => $foodbank->organization_name ?? 'Unknown',
                'foodbank_id' => $donation->foodbank_id,
                'status' => $donation->status,
                'status_display' => ucfirst(str_replace('_', ' ', $donation->status)),
                'description' => $donation->description,
                'expiry_date' => $donation->expiry_date ? $donation->expiry_date->format('F d, Y') : 'N/A',
                'scheduled_date' => $donation->scheduled_date ? $donation->scheduled_date->format('F d, Y') : 'N/A',
                'scheduled_time' => $donation->scheduled_time ? (is_string($donation->scheduled_time) ? substr($donation->scheduled_time, 0, 5) : $donation->scheduled_time->format('H:i')) : 'N/A',
                'pickup_method' => ucfirst($donation->pickup_method),
                'collected_at' => $donation->collected_at ? $donation->collected_at->format('F d, Y H:i') : 'N/A',
                'sort_date' => $donation->collected_at ?? $donation->created_at,
            ];
        });

        // Format DonationRequest records
        $formattedRequests = $requestQuery->get()->map(function ($donationRequest) {
            $foodbank = $donationRequest->foodbank;
            $formatted = \App\Services\DonationRequestService::formatRequestData($donationRequest);
            
            // Format scheduled date
            $scheduledDate = $donationRequest->scheduled_date ?? $donationRequest->dropoff_date ?? $donationRequest->created_at;
            $scheduledDateDisplay = $scheduledDate instanceof \Carbon\Carbon 
                ? $scheduledDate->format('F d, Y') 
                : ($scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('F d, Y') : 'N/A');
            
            return [
                'id' => $donationRequest->donation_request_id,
                'type' => 'request',
                'donation_number' => $donationRequest->donation ? $donationRequest->donation->donation_number : 'REQ-' . substr($donationRequest->donation_request_id, 0, 8),
                'item_name' => $donationRequest->item_name,
                'category' => $donationRequest->category,
                'quantity' => $donationRequest->quantity,
                'unit' => $donationRequest->unit ?? 'pcs',
                'date_donated' => $donationRequest->created_at->format('F d, Y'),
                'date_donated_raw' => $donationRequest->created_at->format('Y-m-d'),
                'foodbank_name' => $foodbank->organization_name ?? 'Unknown',
                'foodbank_id' => $donationRequest->foodbank_id,
                'status' => $donationRequest->status,
                'status_display' => \App\Services\DonationRequestService::getStatusDisplay($donationRequest->status),
                'description' => $donationRequest->description,
                'expiry_date' => $donationRequest->expiry_date ? $donationRequest->expiry_date->format('F d, Y') : 'N/A',
                'scheduled_date' => $scheduledDateDisplay,
                'scheduled_time' => $formatted['scheduled_time_display'] ?? 'N/A',
                'pickup_method' => 'Pickup',
                'collected_at' => $donationRequest->fulfilled_at ? $donationRequest->fulfilled_at->format('F d, Y H:i') : 'N/A',
                'sort_date' => $donationRequest->fulfilled_at ?? $donationRequest->updated_at ?? $donationRequest->created_at,
            ];
        });

        // Merge and sort by date (most recent first)
        // Convert both to regular collections to avoid Eloquent Collection merge issues
        $allRecords = collect($formattedDonations)->merge(collect($formattedRequests))
            ->sortByDesc(function ($record) {
                return $record['sort_date'] instanceof \Carbon\Carbon 
                    ? $record['sort_date']->timestamp 
                    : \Carbon\Carbon::parse($record['sort_date'])->timestamp;
            })
            ->values();

        // Create a custom paginator
        $currentPage = $request->get('page', 1);
        $perPage = 10;
        $currentItems = $allRecords->slice(($currentPage - 1) * $perPage, $perPage)->values()->toArray();
        $formattedDonations = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $allRecords->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Calculate statistics (include both donations and requests)
        $totalQuantity = $allDonations->sum('quantity') + $allRequests->sum('quantity');
        $allFoodbankIds = $allDonations->pluck('foodbank_id')->merge($allRequests->pluck('foodbank_id'))->unique();
        $stats = [
            'total_donations' => $allDonations->count() + $allRequests->count(),
            'total_quantity' => $totalQuantity,
            'foodbanks_served' => $allFoodbankIds->count(),
        ];

        // Get unique categories from both donations and requests
        $donationCategories = $allDonations->pluck('item_category')->filter()->unique();
        $requestCategories = $allRequests->pluck('category')->filter()->unique();
        $categories = $donationCategories->merge($requestCategories)->unique()->sort()->values();

        return view('establishment.donation-history', compact('user', 'formattedDonations', 'stats', 'categories'));
    }

    /**
     * Export donation history
     */
    public function exportDonationHistory(Request $request, $type)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }

        $establishmentId = Session::get('user_id');

        // Build queries with same filters as donationHistory - include both Donation and DonationRequest
        $donationQuery = Donation::where('establishment_id', $establishmentId)
            ->with(['foodbank']);

        $requestQuery = DonationRequest::where('establishment_id', $establishmentId)
            ->whereIn('status', [
                \App\Services\DonationRequestService::STATUS_ACCEPTED,
                \App\Services\DonationRequestService::STATUS_DECLINED,
                \App\Services\DonationRequestService::STATUS_COMPLETED
            ])
            ->with(['foodbank', 'donation']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $donationQuery->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhereHas('foodbank', function($q) use ($search) {
                      $q->where('organization_name', 'like', "%{$search}%");
                  });
            });
            $requestQuery->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhereHas('foodbank', function($q) use ($search) {
                      $q->where('organization_name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->status;
            $donationStatuses = ['pending_pickup', 'ready_for_collection', 'collected', 'cancelled', 'expired'];
            $requestStatuses = ['accepted', 'declined', 'completed'];
            
            if (in_array($status, $donationStatuses)) {
                $donationQuery->where('status', $status);
            } elseif (in_array($status, $requestStatuses)) {
                $requestQuery->where('status', $status);
            }
        }

        // Apply category filter
        if ($request->filled('category')) {
            $donationQuery->where('item_category', $request->category);
            $requestQuery->where('category', $request->category);
        }

        // Apply date filters
        if ($request->filled('date_from')) {
            $donationQuery->where('created_at', '>=', $request->date_from);
            $requestQuery->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $donationQuery->where('created_at', '<=', $request->date_to . ' 23:59:59');
            $requestQuery->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Get and format both types of records
        $allDonations = $donationQuery->orderBy('created_at', 'desc')->get();
        $allRequests = $requestQuery->orderBy('created_at', 'desc')->get();

        // Format records consistently
        $formattedRecords = collect();
        
        // Format Donation records
        foreach ($allDonations as $donation) {
            $foodbank = $donation->foodbank;
            $formattedRecords->push([
                'type' => 'donation',
                'donation_number' => $donation->donation_number,
                'item_name' => $donation->item_name,
                'category' => $donation->item_category,
                'quantity' => $donation->quantity,
                'unit' => $donation->unit ?? 'pcs',
                'donation_type' => ucfirst($donation->pickup_method ?? 'pickup'),
                'date_donated' => $donation->created_at->format('Y-m-d H:i:s'),
                'scheduled_date' => $donation->scheduled_date ? $donation->scheduled_date->format('Y-m-d') : 'N/A',
                'scheduled_time' => $donation->scheduled_time ? (is_string($donation->scheduled_time) ? substr($donation->scheduled_time, 0, 5) : $donation->scheduled_time->format('H:i')) : 'N/A',
                'expiry_date' => $donation->expiry_date ? $donation->expiry_date->format('Y-m-d') : 'N/A',
                'collected_date' => $donation->collected_at ? $donation->collected_at->format('Y-m-d H:i:s') : 'N/A',
                'status' => ucfirst(str_replace('_', ' ', $donation->status)),
                'recipient' => $foodbank->organization_name ?? 'Unknown',
                'description' => $donation->description ?? '',
                'sort_date' => $donation->collected_at ?? $donation->created_at,
            ]);
        }

        // Format DonationRequest records
        foreach ($allRequests as $donationRequest) {
            $foodbank = $donationRequest->foodbank;
            $formatted = \App\Services\DonationRequestService::formatRequestData($donationRequest);
            
            $scheduledDate = $donationRequest->scheduled_date ?? $donationRequest->dropoff_date ?? $donationRequest->created_at;
            $scheduledDateFormatted = $scheduledDate instanceof \Carbon\Carbon 
                ? $scheduledDate->format('Y-m-d') 
                : ($scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d') : 'N/A');
            
            // Format scheduled time - use time_display which handles allDay, anytime, or time ranges
            $scheduledTimeFormatted = $formatted['time_display'] ?? 'N/A';
            if ($scheduledTimeFormatted === 'N/A' && $formatted['scheduled_time_display'] && $formatted['scheduled_time_display'] !== 'N/A') {
                $scheduledTimeFormatted = $formatted['scheduled_time_display'];
            }
            
            // Format expiry date - check if it exists
            $expiryDateFormatted = 'N/A';
            if ($donationRequest->expiry_date) {
                $expiryDateFormatted = $donationRequest->expiry_date instanceof \Carbon\Carbon 
                    ? $donationRequest->expiry_date->format('Y-m-d') 
                    : \Carbon\Carbon::parse($donationRequest->expiry_date)->format('Y-m-d');
            }
            
            // Format collected date - only show if request is completed/fulfilled
            $collectedDateFormatted = 'N/A';
            if ($donationRequest->fulfilled_at) {
                $collectedDateFormatted = $donationRequest->fulfilled_at instanceof \Carbon\Carbon 
                    ? $donationRequest->fulfilled_at->format('Y-m-d H:i:s') 
                    : \Carbon\Carbon::parse($donationRequest->fulfilled_at)->format('Y-m-d H:i:s');
            } elseif ($donationRequest->status === \App\Services\DonationRequestService::STATUS_COMPLETED && $donationRequest->donation) {
                // If completed but no fulfilled_at, use donation's collected_at
                $donation = $donationRequest->donation;
                if ($donation->collected_at) {
                    $collectedDateFormatted = $donation->collected_at instanceof \Carbon\Carbon 
                        ? $donation->collected_at->format('Y-m-d H:i:s') 
                        : \Carbon\Carbon::parse($donation->collected_at)->format('Y-m-d H:i:s');
                }
            }
            
            $formattedRecords->push([
                'type' => 'request',
                'donation_number' => $donationRequest->donation ? $donationRequest->donation->donation_number : 'REQ-' . substr($donationRequest->donation_request_id, 0, 8),
                'item_name' => $donationRequest->item_name,
                'category' => $donationRequest->category,
                'quantity' => $donationRequest->quantity,
                'unit' => $donationRequest->unit ?? 'pcs',
                'donation_type' => 'Pickup',
                'date_donated' => $donationRequest->created_at->format('Y-m-d H:i:s'),
                'scheduled_date' => $scheduledDateFormatted,
                'scheduled_time' => $scheduledTimeFormatted,
                'expiry_date' => $expiryDateFormatted,
                'collected_date' => $collectedDateFormatted,
                'status' => \App\Services\DonationRequestService::getStatusDisplay($donationRequest->status),
                'recipient' => $foodbank->organization_name ?? 'Unknown',
                'description' => $donationRequest->description ?? '',
                'sort_date' => $donationRequest->fulfilled_at ?? $donationRequest->updated_at ?? $donationRequest->created_at,
            ]);
        }

        // Sort by date (most recent first)
        $formattedRecords = $formattedRecords->sortByDesc(function ($record) {
            return $record['sort_date'] instanceof \Carbon\Carbon 
                ? $record['sort_date']->timestamp 
                : \Carbon\Carbon::parse($record['sort_date'])->timestamp;
        })->values();

        switch ($type) {
            case 'csv':
                return $this->exportToCsv($formattedRecords);
            case 'excel':
                return $this->exportToExcel($formattedRecords);
            case 'pdf':
                return $this->exportToPdf($formattedRecords);
            default:
                return redirect()->back()->with('error', 'Invalid export type.');
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($records)
    {
        $filename = 'donation_history_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        // Add BOM for Excel compatibility
        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Donation Number',
                'Item Name',
                'Category',
                'Quantity',
                'Unit',
                'Donation Type',
                'Date Donated',
                'Scheduled Date',
                'Scheduled Time',
                'Expiry Date',
                'Collected Date',
                'Status',
                'Recipient (Foodbank)',
                'Description'
            ]);

            // Data rows
            foreach ($records as $record) {
                fputcsv($file, [
                    $record['donation_number'],
                    $record['item_name'],
                    ucfirst($record['category']),
                    $record['quantity'],
                    $record['unit'],
                    $record['donation_type'],
                    $record['date_donated'],
                    $record['scheduled_date'],
                    $record['scheduled_time'],
                    $record['expiry_date'],
                    $record['collected_date'],
                    $record['status'],
                    $record['recipient'],
                    $record['description']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($records)
    {
        $filename = 'donation_history_' . date('Y-m-d_His') . '.xlsx';
        
        $data = [];
        
        // Headers
        $data[] = [
            'Donation Number',
            'Item Name',
            'Category',
            'Quantity',
            'Unit',
            'Donation Type',
            'Date Donated',
            'Scheduled Date',
            'Scheduled Time',
            'Expiry Date',
            'Collected Date',
            'Status',
            'Recipient (Foodbank)',
            'Description'
        ];
        
        // Data rows
        foreach ($records as $record) {
            $data[] = [
                $record['donation_number'],
                $record['item_name'],
                ucfirst($record['category']),
                $record['quantity'],
                $record['unit'],
                $record['donation_type'],
                $record['date_donated'],
                $record['scheduled_date'],
                $record['scheduled_time'],
                $record['expiry_date'],
                $record['collected_date'],
                $record['status'],
                $record['recipient'],
                $record['description']
            ];
        }
        
        // Create Excel file using Maatwebsite Excel (v1.1.5 uses PHPExcel)
        // The export() method handles the download response
        return Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Donation History', function($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
                
                // Style the header row
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                    $row->setBackground('#2d5016');
                    $row->setFontColor('#ffffff');
                });
                
                // Auto-size columns
                foreach(range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            });
        })->export('xlsx');
    }

    /**
     * Export to PDF
     */
    private function exportToPdf($records)
    {
        $user = $this->getUserData();
        $establishmentName = $user->business_name ?? 'Establishment';
        
        // Format data for PDF view
        $data = $records->map(function ($record) {
            // Helper function to safely parse dates
            $parseDate = function($dateString, $format = 'F d, Y') {
                if ($dateString === 'N/A' || empty($dateString)) {
                    return 'N/A';
                }
                try {
                    return \Carbon\Carbon::parse($dateString)->format($format);
                } catch (\Exception $e) {
                    return 'N/A';
                }
            };
            
            return [
                'donation_number' => $record['donation_number'],
                'item_name' => $record['item_name'],
                'category' => ucfirst($record['category']),
                'quantity' => $record['quantity'],
                'unit' => $record['unit'],
                'donation_type' => $record['donation_type'],
                'date_donated' => $parseDate($record['date_donated'], 'F d, Y H:i'),
                'scheduled_date' => $parseDate($record['scheduled_date'], 'F d, Y'),
                'scheduled_time' => $record['scheduled_time'] !== 'N/A' ? $record['scheduled_time'] : 'N/A',
                'expiry_date' => $parseDate($record['expiry_date'], 'F d, Y'),
                'collected_at' => $parseDate($record['collected_date'], 'F d, Y H:i'),
                'foodbank' => $record['recipient'],
                'status' => $record['status'],
                'description' => $record['description'] ?: 'N/A',
            ];
        })->toArray();

        $filename = 'donation_history_' . date('Y-m-d_His') . '.pdf';
        
        $pdf = Pdf::loadView('establishment.donation-history-pdf', [
            'data' => $data,
            'establishmentName' => $establishmentName,
            'exportDate' => now()->format('F d, Y'),
            'totalDonations' => count($data)
        ]);
        
        return $pdf->download($filename);
    }

    /**
     * Get reviews for a specific food listing
     */
    public function getFoodListingReviews($id)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $establishmentId = Session::get('user_id');
        
        // Verify the food listing belongs to this establishment
        $foodListing = FoodListing::where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->first();

        if (!$foodListing) {
            return response()->json(['error' => 'Food listing not found'], 404);
        }

        // Get reviews for this food listing (optimized query)
        // First get total count and average rating efficiently
        $totalReviews = Review::where('food_listing_id', $id)->count();
        $averageRating = $totalReviews > 0 
            ? round(Review::where('food_listing_id', $id)->avg('rating'), 1) 
            : 0;
        
        // Get reviews with consumer data (limit to recent reviews for performance)
        $reviewsData = Review::with(['consumer' => function($query) {
                $query->select('consumer_id', 'fname', 'lname');
            }])
            ->where('food_listing_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(50) // Limit to 50 most recent reviews for performance
            ->get();

        // Format reviews for display
        $reviews = $reviewsData->map(function ($review) {
            $consumer = $review->consumer;
            $userName = 'Anonymous';
            
            if ($consumer) {
                $fname = trim($consumer->fname ?? '');
                $lname = trim($consumer->lname ?? '');
                $userName = trim($fname . ' ' . $lname) ?: 'Anonymous';
            }

            return [
                'id' => $review->id,
                'user_name' => $userName,
                'avatar' => null,
                'rating' => $review->rating,
                'comment' => $review->description ?? '',
                'date' => $review->created_at->format('Y-m-d'),
                'image_path' => $review->image_path ? Storage::url($review->image_path) : null,
                'video_path' => $review->video_path ? Storage::url($review->video_path) : null,
                'flagged' => $review->flagged ?? false,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
            'average_rating' => $averageRating,
            'total_reviews' => $totalReviews,
        ]);
    }
}
