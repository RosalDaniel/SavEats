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
use App\Models\HelpCenterArticle;
use App\Services\NotificationService;
 use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        
        // Get all food listings for this establishment (single query, reused for multiple calculations)
        $allListings = FoodListing::where('establishment_id', $establishmentId)->get();
        
        // Calculate inventory health statistics
        $totalItems = $allListings->count();
        
        if ($totalItems > 0) {
            // Fresh Stock: Items that are not expired and not expiring soon (expiry > 3 days away)
            $freshStock = $allListings->filter(function ($item) {
                $isExpired = $item->expiry_date < now()->toDateString();
                $isExpiringSoon = $item->expiry_date <= now()->addDays(3)->toDateString();
                return !$isExpired && !$isExpiringSoon;
            })->count();
            
            // Expiring Stock: Items expiring within next 3 days (but not expired)
            $expiringStock = $allListings->filter(function ($item) {
                $isExpired = $item->expiry_date < now()->toDateString();
                $isExpiringSoon = $item->expiry_date >= now()->toDateString() &&
                                  $item->expiry_date <= now()->addDays(3)->toDateString();
                return !$isExpired && $isExpiringSoon;
            })->count();
            
            // Expired Stock: Items that have passed expiry date
            $expiredStock = $allListings->filter(function ($item) {
                return $item->expiry_date < now()->toDateString();
            })->count();
            
            // Calculate percentages
            $freshStockPercent = round(($freshStock / $totalItems) * 100);
            $expiringStockPercent = round(($expiringStock / $totalItems) * 100);
            $expiredStockPercent = round(($expiredStock / $totalItems) * 100);
        } else {
            $freshStockPercent = 0;
            $expiringStockPercent = 0;
            $expiredStockPercent = 0;
        }
        
        // Get expiring food listings (expiring within next 3 days, not expired)
        $expiringItems = $allListings
            ->filter(function ($item) {
                return $item->expiry_date >= now()->toDateString() &&
                       $item->expiry_date <= now()->addDays(3)->toDateString();
            })
            ->sortBy('expiry_date')
            ->take(5)
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
        
        $inventoryHealth = [
            'fresh_stock_percent' => $freshStockPercent,
            'expiring_stock_percent' => $expiringStockPercent,
            'expired_stock_percent' => $expiredStockPercent,
        ];
        
        // Active Listings: Count items that are not expired and not expiring soon
        $activeListings = $allListings->filter(function ($item) {
            $isExpired = $item->expiry_date < now()->toDateString();
            $isExpiringSoon = $item->expiry_date <= now()->addDays(3)->toDateString();
            return !$isExpired && !$isExpiringSoon;
        })->count();
        
        // Today's Earnings: Sum of completed orders for today
        $todayEarnings = Order::where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->whereDate('completed_at', now()->toDateString())
            ->sum('total_price') ?? 0.0;
        
        // Food Donated: Placeholder for future donation system
        $foodDonated = 0;
        
        // Food Saved: Sum of sold_stock from food listings
        $foodSaved = $allListings->sum('sold_stock') ?? 0;
        
        // Ensure all values are properly initialized (handle null values from database)
        $dashboardStats = [
            'active_listings' => (int) $activeListings,
            'today_earnings' => $todayEarnings,
            'food_donated' => (int) $foodDonated,
            'food_saved' => $foodSaved,
        ];
        
        // Get the most recent pending order
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
        
        $reviewsData = [
            'average_rating' => $averageRating,
            'total_reviews' => $totalReviews,
            'positive_reviews' => $positiveReviews,
            'negative_reviews' => $negativeReviews,
            'reviews_this_month' => $reviewsThisMonth,
            'positive_percentage' => $positivePercentage,
            'rating_text' => $ratingText,
        ];
        
        return view('establishment.dashboard', compact('user', 'expiringItems', 'inventoryHealth', 'dashboardStats', 'pendingOrderData', 'reviewsData'));
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
        
        // Get real food listings from database
        $foodItems = FoodListing::where('establishment_id', $establishmentId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
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
                    'status' => $item->is_expired ? 'expired' : ($item->expiry_date <= now()->addDays(3) ? 'expiring' : 'active'),
                    'image' => $item->image_path ? Storage::url($item->image_path) : 'https://via.placeholder.com/40x40/4a7c59/ffffff?text=' . strtoupper(substr($item->name, 0, 1)),
                    'pickup_available' => $item->pickup_available,
                    'delivery_available' => $item->delivery_available,
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

        return view('establishment.listing-management', compact('user', 'foodItems', 'stats', 'establishmentAddress'));
    }

    public function orderManagement()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        // Get establishment data
        $establishmentId = Session::get('user_id');
        $establishment = Establishment::find($establishmentId);
        
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
                
                // Format pickup date (use created_at date for pickup date)
                $pickupDate = $order->created_at ? $order->created_at->format('F d, Y') : null;
                
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
            'accepted' => count(array_filter($orders, fn($order) => $order['status'] === 'accepted' && !$order['is_missed_pickup'])),
            'missed_pickup' => count(array_filter($orders, fn($order) => $order['is_missed_pickup'])),
            'completed' => count(array_filter($orders, fn($order) => $order['status'] === 'completed')),
            'cancelled' => count(array_filter($orders, fn($order) => $order['status'] === 'cancelled'))
        ];

        return view('establishment.order-management', compact('establishment', 'orders', 'orderCounts'));
    }

    public function announcements()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        
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

        $establishmentId = Session::get('user_id');
        $order = Order::where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or cannot be accepted'], 404);
        }

        DB::beginTransaction();
        try {
            // Move stock from reserved to sold
            // reserved_stock -= qty, sold_stock += qty
            if ($order->foodListing) {
                $foodListing = $order->foodListing;
                $foodListing->reserved_stock = max(0, ($foodListing->reserved_stock ?? 0) - $order->quantity);
                $foodListing->sold_stock = ($foodListing->sold_stock ?? 0) + $order->quantity;
                $foodListing->save();
            }

            $order->status = 'accepted';
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
                'error' => 'Failed to accept order: ' . $e->getMessage()
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
            // Restore stock using StockService (idempotent)
            $stockService = new \App\Services\StockService();
            $stockResult = $stockService->restoreStock($order, $request->input('reason', 'Cancelled by establishment'));
            
            if (!$stockResult['success']) {
                throw new \Exception($stockResult['message']);
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
                'error' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled and quantity restored successfully'
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

        $establishmentId = Session::get('user_id');
        $order = Order::with('foodListing')
            ->where('id', $id)
            ->where('establishment_id', $establishmentId)
            ->where('status', 'accepted')
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found or cannot be completed'], 404);
        }

        DB::beginTransaction();
        try {
            // Stock is already deducted when payment is confirmed
            // Completing order just changes status - no stock movement needed
            $order->status = 'completed';
            $order->completed_at = now();
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

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'effective_status' => $order->effective_status,
                'created_at' => $order->created_at->format('F d, Y | g:i A'),
                'payment_method' => ucfirst($order->payment_method),
                'delivery_method' => ucfirst($order->delivery_method),
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->consumer->email ?? 'N/A',
                'delivery_address' => $order->delivery_address,
                'pickup_start_time' => $order->pickup_start_time ? (is_string($order->pickup_start_time) ? substr($order->pickup_start_time, 0, 5) : $order->pickup_start_time->format('H:i')) : null,
                'pickup_end_time' => $order->pickup_end_time ? (is_string($order->pickup_end_time) ? substr($order->pickup_end_time, 0, 5) : $order->pickup_end_time->format('H:i')) : null,
                'items' => [
                    [
                        'name' => $order->foodListing->name,
                        'quantity' => $order->quantity,
                        'unit_price' => (float) $order->unit_price,
                        'total_price' => (float) $order->total_price,
                    ]
                ],
                'subtotal' => (float) $order->total_price,
                'delivery_fee' => $order->delivery_method === 'delivery' ? 57.00 : 0.00,
                'total' => (float) $order->total_price + ($order->delivery_method === 'delivery' ? 57.00 : 0.00),
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
        $totalEarnings = Order::where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->sum('total_price');
        
        // Get completed orders for display
        $completedOrders = Order::with(['foodListing', 'consumer'])
            ->where('establishment_id', $establishmentId)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'product_name' => $order->foodListing->name ?? 'Unknown Product',
                    'quantity' => $order->quantity,
                    'unit_price' => (float) $order->unit_price,
                    'total_price' => (float) $order->total_price,
                    'customer_name' => $order->customer_name,
                    'payment_method' => $this->formatPaymentMethod($order->payment_method),
                    'completed_at' => $order->completed_at ? $order->completed_at->format('M d, Y') : null,
                    'created_at' => $order->created_at->format('M d, Y'),
                ];
            })
            ->toArray();

        // Calculate daily earnings (last 7 days)
        $dailyEarnings = [];
        // Map day of week (0=Sunday, 1=Monday, etc.) to labels
        $dayLabels = ['SUN', 'M', 'T', 'W', 'TH', 'FRI', 'SAT'];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayOfWeek = $date->dayOfWeek; // 0=Sunday, 1=Monday, etc.
            $dayEarnings = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereDate('completed_at', $date->toDateString())
                ->sum('total_price');
            
            $dailyEarnings[] = [
                'label' => $dayLabels[$dayOfWeek],
                'value' => (float) $dayEarnings
            ];
        }

        // Calculate monthly earnings (last 12 months)
        $monthlyEarnings = [];
        $monthLabels = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthEarnings = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $date->year)
                ->whereMonth('completed_at', $date->month)
                ->sum('total_price');
            
            $monthlyEarnings[] = [
                'label' => $monthLabels[$date->month - 1],
                'value' => (float) $monthEarnings
            ];
        }

        // Calculate yearly earnings (last 5 years)
        $yearlyEarnings = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            $yearEarnings = Order::where('establishment_id', $establishmentId)
                ->where('status', 'completed')
                ->whereYear('completed_at', $year)
                ->sum('total_price');
            
            $yearlyEarnings[] = [
                'label' => (string) $year,
                'value' => (float) $yearEarnings
            ];
        }

        return view('establishment.earnings', compact(
            'user', 
            'totalEarnings', 
            'completedOrders',
            'dailyEarnings',
            'monthlyEarnings',
            'yearlyEarnings'
        ));
    }

    public function donationHub()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $user = $this->getUserData();
        
        // Fetch all active donation requests from food banks
        $donationRequests = DonationRequest::whereIn('status', ['pending', 'active'])
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
                    'foodbank_name' => $foodbank->organization_name ?? 'Food Bank',
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
                    'delivery_option' => $request->delivery_option,
                    'delivery_option_display' => ucfirst($request->delivery_option),
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
                ];
            })
            ->toArray();
        
        return view('establishment.donation-hub', compact(
            'user',
            'donationRequests',
            'foodbanks'
        ));
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

        $establishmentId = Session::get('user_id');

        try {
            $donationRequest = DonationRequest::where('donation_request_id', $requestId)
                ->whereIn('status', ['pending', 'active'])
                ->first();

            if (!$donationRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Donation request not found or already fulfilled.'
                ], 404);
            }

            // Validate the donation details
            $validated = $request->validate([
                'item_name' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'unit' => 'required|string|max:20',
                'category' => 'required|string',
                'description' => 'nullable|string',
                'expiry_date' => 'nullable|date|after_or_equal:today',
                'scheduled_date' => 'required|date|after_or_equal:today',
                'scheduled_time' => 'nullable|date_format:H:i',
                'pickup_method' => 'required|in:pickup,delivery',
                'establishment_notes' => 'nullable|string',
            ]);

            // Create the donation linked to this request
            $donation = Donation::create([
                'foodbank_id' => $donationRequest->foodbank_id,
                'establishment_id' => $establishmentId,
                'donation_request_id' => $donationRequest->donation_request_id,
                'item_name' => $validated['item_name'],
                'item_category' => $validated['category'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'description' => $validated['description'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'status' => 'pending_pickup',
                'pickup_method' => $validated['pickup_method'],
                'scheduled_date' => $validated['scheduled_date'],
                'scheduled_time' => $validated['scheduled_time'] ?? null,
                'establishment_notes' => $validated['establishment_notes'] ?? null,
                'is_urgent' => false,
                'is_nearing_expiry' => false,
            ]);

            // Update the donation request
            $donationRequest->fulfilled_by_establishment_id = $establishmentId;
            $donationRequest->fulfilled_at = now();
            $donationRequest->donation_id = $donation->donation_id;
            $donationRequest->status = 'completed';
            $donationRequest->matches = ($donationRequest->matches ?? 0) + 1;
            $donationRequest->save();

            // Reload donation with relationships for notification
            $donation->load(['foodbank', 'establishment']);
            
            // Send notification to foodbank
            NotificationService::notifyDonationCreated($donation);

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
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Please login as an establishment.'
            ], 403);
        }

        $establishmentId = Session::get('user_id');

        // Validate the request
        $validated = $request->validate([
            'foodbank_id' => 'required|uuid|exists:foodbanks,foodbank_id',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:20',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'pickup_method' => 'required|in:pickup,delivery',
            'establishment_notes' => 'nullable|string',
        ]);

        try {
            // Create the donation
            $donation = Donation::create([
                'foodbank_id' => $validated['foodbank_id'],
                'establishment_id' => $establishmentId,
                'donation_request_id' => null, // Not linked to a specific request
                'item_name' => $validated['item_name'],
                'item_category' => $validated['category'],
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'description' => $validated['description'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'status' => 'pending_pickup',
                'pickup_method' => $validated['pickup_method'],
                'scheduled_date' => $validated['scheduled_date'],
                'scheduled_time' => $validated['scheduled_time'] ?? null,
                'establishment_notes' => $validated['establishment_notes'] ?? null,
                'is_urgent' => false,
                'is_nearing_expiry' => false,
            ]);
            
            // Reload donation with relationships for notification
            $donation->load(['foodbank', 'establishment']);
            
            // Send notification to foodbank
            NotificationService::notifyDonationCreated($donation);

            return response()->json([
                'success' => true,
                'message' => 'Donation request submitted successfully! The foodbank will review your request.',
                'data' => [
                    'id' => $donation->donation_id,
                    'donation_number' => $donation->donation_number,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit donation request. Please try again.',
                'error' => $e->getMessage()
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
        
        // Calculate Food Donated: expired items that weren't sold
        $foodDonated = FoodListing::where('establishment_id', $establishmentId)
            ->where('expiry_date', '<', now()->toDateString())
            ->where(function($query) {
                $query->whereNull('sold_stock')
                      ->orWhere('sold_stock', 0);
            })
            ->sum('quantity');
        
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
        
        // Calculate top donated items by category
        $topDonatedItems = FoodListing::where('establishment_id', $establishmentId)
            ->where('expiry_date', '<', now()->toDateString())
            ->where(function($query) {
                $query->whereNull('sold_stock')
                      ->orWhere('sold_stock', 0);
            })
            ->select('category', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('category')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'category' => $item->category,
                    'quantity' => (int) $item->total_quantity
                ];
            })
            ->toArray();
        
        // Calculate total for percentage calculation
        $totalDonated = array_sum(array_column($topDonatedItems, 'quantity'));
        
        // Add percentages to top donated items
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

    public function settings()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        $establishmentId = Session::get('user_id');
        $userData = Establishment::find($establishmentId);
        
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

        // Get published help articles
        $articles = HelpCenterArticle::published()
            ->orderBy('display_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get unique categories
        $categories = HelpCenterArticle::published()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        return view('establishment.help', compact('articles', 'categories'));
    }

    /**
     * Store a new food listing
     */
    public function storeFoodListing(Request $request)
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return response()->json(['error' => 'Unauthorized'], 401);
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
            'pickup' => 'nullable|in:0,1,true,false',
            'delivery' => 'nullable|in:0,1,true,false',
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
                'pickup_available' => in_array($data['pickup'], ['1', 1, 'true', true]),
                'delivery_available' => in_array($data['delivery'], ['1', 1, 'true', true]),
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
            'pickup' => 'nullable|in:0,1,true,false',
            'delivery' => 'nullable|in:0,1,true,false',
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

            $foodListing->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'category' => $data['category'],
                'quantity' => $data['quantity'],
                'original_price' => $data['original_price'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discounted_price' => $discountedPrice,
                'expiry_date' => $data['expiry_date'],
                'address' => $data['address'],
                'pickup_available' => in_array($data['pickup'], ['1', 1, 'true', true]),
                'delivery_available' => in_array($data['delivery'], ['1', 1, 'true', true]),
                'image_path' => $data['image_path'] ?? $foodListing->image_path,
            ]);

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

        // Build query with filters
        $query = Donation::where('establishment_id', $establishmentId)
            ->with(['foodbank']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhereHas('foodbank', function($q) use ($search) {
                      $q->where('organization_name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Get donations
        $donations = $query->orderBy('created_at', 'desc')->get();

        // Format donations for view
        $formattedDonations = $donations->map(function ($donation) {
            $foodbank = $donation->foodbank;
            
            return [
                'id' => $donation->donation_id,
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
            ];
        })->toArray();

        // Calculate statistics
        $allDonations = Donation::where('establishment_id', $establishmentId)->get();
        $stats = [
            'total_donations' => $allDonations->count(),
            'total_quantity' => $allDonations->sum('quantity'),
            'foodbanks_served' => $allDonations->pluck('foodbank_id')->unique()->count(),
        ];

        return view('establishment.donation-history', compact('user', 'formattedDonations', 'stats'));
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

        // Build query with same filters as donationHistory
        $query = Donation::where('establishment_id', $establishmentId)
            ->with(['foodbank']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhereHas('foodbank', function($q) use ($search) {
                      $q->where('organization_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $donations = $query->orderBy('created_at', 'desc')->get();

        switch ($type) {
            case 'csv':
                return $this->exportToCsv($donations);
            case 'excel':
                return $this->exportToExcel($donations);
            case 'pdf':
                return $this->exportToPdf($donations);
            default:
                return redirect()->back()->with('error', 'Invalid export type.');
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($donations)
    {
        $filename = 'donation_history_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        // Add BOM for Excel compatibility
        $callback = function() use ($donations) {
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
                'Date Donated',
                'Recipient (Foodbank)',
                'Status',
                'Scheduled Date',
                'Scheduled Time',
                'Pickup Method',
                'Expiry Date',
                'Description'
            ]);

            // Data rows
            foreach ($donations as $donation) {
                $foodbank = $donation->foodbank;
                fputcsv($file, [
                    $donation->donation_number,
                    $donation->item_name,
                    ucfirst($donation->item_category),
                    $donation->quantity,
                    $donation->unit,
                    $donation->created_at->format('Y-m-d H:i:s'),
                    $foodbank->organization_name ?? 'Unknown',
                    ucfirst(str_replace('_', ' ', $donation->status)),
                    $donation->scheduled_date ? $donation->scheduled_date->format('Y-m-d') : 'N/A',
                    $donation->scheduled_time ? (is_string($donation->scheduled_time) ? substr($donation->scheduled_time, 0, 5) : $donation->scheduled_time->format('H:i')) : 'N/A',
                    ucfirst($donation->pickup_method),
                    $donation->expiry_date ? $donation->expiry_date->format('Y-m-d') : 'N/A',
                    $donation->description ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (CSV with .xlsx extension for compatibility)
     */
    private function exportToExcel($donations)
    {
        // For simplicity, we'll use CSV format but with .xlsx extension
        // In production, you might want to use a library like PhpSpreadsheet
        return $this->exportToCsv($donations);
    }

    /**
     * Export to PDF
     */
    private function exportToPdf($donations)
    {
        // For PDF export, we'll return a simple HTML view that can be printed as PDF
        // In production, you might want to use a library like DomPDF or TCPDF
        $data = $donations->map(function ($donation) {
            $foodbank = $donation->foodbank;
            return [
                'donation_number' => $donation->donation_number,
                'item_name' => $donation->item_name,
                'category' => ucfirst($donation->item_category),
                'quantity' => $donation->quantity . ' ' . $donation->unit,
                'date_donated' => $donation->created_at->format('F d, Y'),
                'foodbank' => $foodbank->organization_name ?? 'Unknown',
                'status' => ucfirst(str_replace('_', ' ', $donation->status)),
            ];
        })->toArray();

        $html = view('establishment.donation-history-pdf', compact('data'))->render();
        
        return response()->make($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="donation_history_' . date('Y-m-d_His') . '.html"',
        ]);
    }
}
